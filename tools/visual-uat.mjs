import fs from 'node:fs/promises';
import fsSync from 'node:fs';
import path from 'node:path';
import process from 'node:process';
import { chromium } from 'playwright';

const baseUrl = process.env.SSZ_LOCAL_URL ?? 'http://swimshop-zimbabwe.local/';
const widths = [360, 390, 430, 768, 1024, 1120, 1121, 1280, 1440, 1920];
const outputDir = path.resolve('artifacts/uat');
const requireLocalMenu = process.env.SSZ_REQUIRE_LOCAL_MENU === '1';

const fallbackSources = await Promise.all([
  fs.readFile(path.resolve('theme/swimshop-zimbabwe/inc/navigation.php'), 'utf8'),
  fs.readFile(path.resolve('theme/swimshop-zimbabwe/template-parts/header/desktop-navigation.php'), 'utf8'),
  fs.readFile(path.resolve('theme/swimshop-zimbabwe/template-parts/header/mobile-navigation.php'), 'utf8'),
]);
const fallbackContract = {
  sharedRenderer: fallbackSources[0].includes('function ssz_primary_menu_fallback('),
  desktopClass: fallbackSources[0].includes("'ssz-primary-menu'") && fallbackSources[1].includes('ssz_primary_menu_fallback_desktop'),
  mobileClass: fallbackSources[0].includes("'ssz-mobile-menu'") && fallbackSources[2].includes('ssz_primary_menu_fallback_mobile'),
};

const browserCandidates = [
  process.env.SSZ_BROWSER_PATH,
  process.env.ProgramFiles && path.join(process.env.ProgramFiles, 'Google', 'Chrome', 'Application', 'chrome.exe'),
  process.env['ProgramFiles(x86)'] && path.join(process.env['ProgramFiles(x86)'], 'Google', 'Chrome', 'Application', 'chrome.exe'),
  process.env.ProgramFiles && path.join(process.env.ProgramFiles, 'Microsoft', 'Edge', 'Application', 'msedge.exe'),
  process.env['ProgramFiles(x86)'] && path.join(process.env['ProgramFiles(x86)'], 'Microsoft', 'Edge', 'Application', 'msedge.exe'),
].filter(Boolean);

const executablePath = browserCandidates.find((candidate) => fsSync.existsSync(candidate));

if (!executablePath) {
  throw new Error('No Chrome or Edge executable found. Set SSZ_BROWSER_PATH to a local browser executable.');
}

await fs.mkdir(outputDir, { recursive: true });

const visible = async (locator) => (await locator.count()) > 0 && await locator.first().isVisible();

const findKeyboardFocus = async (page, predicate) => {
  await page.mouse.click(1, 1);
  for (let attempt = 0; attempt < 120; attempt += 1) {
    await page.keyboard.press('Tab');
    const focus = await page.evaluate(() => {
      const element = document.activeElement;
      if (!element) return null;
      const style = window.getComputedStyle(element);
      return {
        tag: element.tagName,
        id: element.id,
        className: element.className,
        inFooter: Boolean(element.closest('.ssz-footer')),
        isSearch: Boolean(element.matches('[data-search-toggle]')),
        focusVisible: element.matches(':focus-visible'),
        outlineWidth: style.outlineWidth,
        outlineStyle: style.outlineStyle,
        outlineColor: style.outlineColor,
      };
    });

    if (focus && predicate(focus)) return focus;
  }
  return null;
};

const browser = await chromium.launch({ headless: true, executablePath });
const results = [];

try {
  for (const width of widths) {
    const height = width < 768 ? 800 : 900;
    const context = await browser.newContext({ viewport: { width, height } });
    const page = await context.newPage();
    const consoleErrors = [];
    const pageErrors = [];

    page.on('console', (message) => {
      if (message.type() === 'error') consoleErrors.push(message.text());
    });
    page.on('pageerror', (error) => pageErrors.push(error.message));

    let response;
    let navigationError = null;
    try {
      response = await page.goto(baseUrl, { waitUntil: 'networkidle', timeout: 30000 });
    } catch (error) {
      navigationError = error.message;
    }

    const pageState = await page.evaluate(() => {
      const root = document.documentElement;
      const body = document.body;
      return {
        innerWidth: window.innerWidth,
        clientWidth: root?.clientWidth ?? 0,
        scrollWidth: Math.max(root?.scrollWidth ?? 0, body?.scrollWidth ?? 0),
        themeVisible: Boolean(document.querySelector('.ssz-site-shell, .ssz-header[data-site-header], .ssz-hero')),
        headerPresent: Boolean(document.querySelector('[data-site-header]')),
      };
    });

    const duplicateIds = await page.evaluate(() => {
      const counts = new Map();
      document.querySelectorAll('[id]').forEach((element) => {
        if (element.id) counts.set(element.id, (counts.get(element.id) ?? 0) + 1);
      });
      return [...counts.entries()].filter(([, count]) => count > 1).map(([id]) => id);
    });

    const headerMode = await page.evaluate(() => {
      const desktopNav = document.querySelector('[data-primary-nav]');
      const menuToggle = document.querySelector('[data-menu-toggle]');
      const announcement = document.querySelector('.ssz-announcement');
      const colorLogo = document.querySelector('.ssz-brand-lockup__symbol');
      const customLogo = document.querySelector('.custom-logo');
      const cart = document.querySelector('.ssz-cart-link');
      const style = (element) => element ? window.getComputedStyle(element) : null;
      return {
        desktopNavVisible: Boolean(desktopNav && style(desktopNav).display !== 'none'),
        mobileToggleVisible: Boolean(menuToggle && style(menuToggle).display !== 'none'),
        announcementPresent: Boolean(announcement),
        colorLogoVisible: Boolean(colorLogo && style(colorLogo).display !== 'none'),
        colorLogoLoaded: Boolean(colorLogo && colorLogo.complete && colorLogo.naturalWidth > 0),
        customLogoVisible: Boolean(customLogo && style(customLogo).display !== 'none'),
        cartVisible: Boolean(cart && style(cart).display !== 'none'),
        accountHref: document.querySelector('.ssz-account-link')?.getAttribute('href') ?? null,
        mobileAccountHref: document.querySelector('.ssz-mobile-account')?.getAttribute('href') ?? null,
      };
    });

    const overflow = pageState.scrollWidth > pageState.clientWidth + 2;
    const search = { opened: false, inputFocused: false, closed: false, focusRestored: false, menuCloses: true };
    const searchToggle = page.locator('[data-search-toggle]');
    const searchPanel = page.locator('[data-search-panel]');
    const searchInput = page.locator('[data-search-panel] input[type="search"]');
    const menuToggle = page.locator('[data-menu-toggle]');
    const mobileNav = page.locator('[data-mobile-nav]');

    if (await searchToggle.count()) {
      await searchToggle.click();
      search.opened = await searchPanel.isVisible();
      search.inputFocused = await page.evaluate(() => document.activeElement?.matches('[data-search-panel] input[type="search"]') ?? false);
      await page.locator('[data-search-close]').click();
      search.closed = !(await searchPanel.isVisible());
      search.focusRestored = await page.evaluate(() => document.activeElement?.matches('[data-search-toggle]') ?? false);
    }

    const menuState = {
      checked: width <= 1120,
      opened: null,
      closedByButton: null,
      closedByEscape: null,
      bodyScrollLock: null,
      focusRestored: null,
      drilldown: { configured: false, opened: null, backedOut: null },
      accordion: { configured: false, expanded: null, collapsed: null },
    };

    if (menuState.checked && await menuToggle.count() && await menuToggle.isVisible()) {
      await menuToggle.click();
      menuState.opened = await mobileNav.isVisible();
      menuState.bodyScrollLock = await page.evaluate(() => getComputedStyle(document.documentElement).overflow === 'hidden' && getComputedStyle(document.body).overflow === 'hidden');
      await page.locator('[data-mobile-close]').click();
      menuState.closedByButton = !(await mobileNav.isVisible());
      await menuToggle.click();
      await page.keyboard.press('Escape');
      menuState.closedByEscape = !(await mobileNav.isVisible());
      menuState.focusRestored = await page.evaluate(() => document.activeElement?.matches('[data-menu-toggle]') ?? false);
    }

    if (menuState.checked && await menuToggle.count() && await menuToggle.isVisible()) {
      await menuToggle.click();
      const drilldown = page.locator('[data-mobile-drilldown]').first();
      menuState.drilldown.configured = (await drilldown.count()) > 0;
      if (menuState.drilldown.configured) {
        const panelId = await drilldown.getAttribute('aria-controls');
        await drilldown.click();
        const panel = page.locator(`#${panelId}`);
        menuState.drilldown.opened = await panel.isVisible();
        const accordion = panel.locator('[data-mobile-accordion]').first();
        menuState.accordion.configured = (await accordion.count()) > 0;
        if (menuState.accordion.configured) {
          const accordionId = await accordion.getAttribute('aria-controls');
          await accordion.click();
          const accordionPanel = page.locator(`#${accordionId}`);
          menuState.accordion.expanded = await accordionPanel.isVisible() && await accordion.getAttribute('aria-expanded') === 'true';
          await accordion.click();
          menuState.accordion.collapsed = !(await accordionPanel.isVisible()) && await accordion.getAttribute('aria-expanded') === 'false';
        }
        await panel.locator('[data-mobile-back]').click();
        menuState.drilldown.backedOut = !(await panel.isVisible());
      }
      await page.locator('[data-mobile-close]').click();
    }

    if (menuState.checked && await searchToggle.count()) {
      await searchToggle.click();
      await menuToggle.click();
      search.menuCloses = !(await searchPanel.isVisible()) && await mobileNav.isVisible();
      await page.keyboard.press('Escape');
    }

    const desktop = {
      configured: false,
      twoConfigured: false,
      opened: null,
      panelWithinViewport: null,
      closedByEscape: null,
      ariaReset: null,
      exclusive: null,
      searchClosesMega: null,
      megaClosesSearch: null,
      searchAriaReset: null,
    };
    if (!menuState.checked) {
      const desktopToggles = page.locator('[data-primary-nav] > .ssz-primary-menu > .ssz-nav-item--has-children > [data-nav-toggle]');
      const desktopToggle = desktopToggles.first();
      const desktopToggleCount = await desktopToggles.count();
      desktop.configured = desktopToggleCount > 0;
      desktop.twoConfigured = desktopToggleCount >= 2;
      if (desktop.configured) {
        const panelId = await desktopToggle.getAttribute('aria-controls');
        const panel = page.locator(`#${panelId}`);
        await desktopToggle.click();
        await page.waitForTimeout(300);
        const box = await panel.boundingBox();
        desktop.opened = await panel.isVisible();
        desktop.panelWithinViewport = Boolean(box && box.x >= 0 && box.x + box.width <= width && box.y >= 0 && box.y + box.height <= height);

        if (desktop.twoConfigured) {
          const secondToggle = desktopToggles.nth(1);
          const secondPanelId = await secondToggle.getAttribute('aria-controls');
          const secondPanel = page.locator(`#${secondPanelId}`);
          await secondToggle.hover();
          await page.waitForTimeout(300);
          desktop.exclusive = !(await panel.isVisible()) && await desktopToggle.getAttribute('aria-expanded') === 'false' && await secondPanel.isVisible();
        }

        await searchToggle.click();
        await page.waitForTimeout(50);
        desktop.searchClosesMega = !(await panel.isVisible()) && await searchPanel.isVisible();
        desktop.searchAriaReset = await desktopToggle.getAttribute('aria-expanded') === 'false';
        await page.locator('[data-search-close]').click();

        await desktopToggle.click();
        await page.waitForTimeout(300);
        desktop.megaClosesSearch = await panel.isVisible() && !(await searchPanel.isVisible()) && await desktopToggle.getAttribute('aria-expanded') === 'true';
        await page.keyboard.press('Escape');
        await page.waitForTimeout(300);
        desktop.closedByEscape = !(await panel.isVisible());
        desktop.ariaReset = await desktopToggle.getAttribute('aria-expanded') === 'false';
      }
    }

    await page.evaluate(() => window.scrollTo(0, 500));
    const stickyHeader = await page.evaluate(() => {
      const header = document.querySelector('[data-site-header]');
      const announcement = document.querySelector('.ssz-announcement');
      if (!header) return { present: false, aboveContent: false, announcementScrolledAway: false };
      const rect = header.getBoundingClientRect();
      const elements = document.elementsFromPoint(rect.left + Math.max(4, rect.width / 2), Math.max(4, rect.top + 4));
      const announcementRect = announcement?.getBoundingClientRect();
      return {
        present: true,
        top: rect.top,
        aboveContent: rect.top <= 1 && rect.bottom > rect.top && elements.some((element) => element.closest?.('[data-site-header]')),
        announcementScrolledAway: !announcementRect || announcementRect.bottom <= 0,
      };
    });

    await page.evaluate(() => window.scrollTo(0, 0));
    const lightFocus = await findKeyboardFocus(page, (focus) => focus.isSearch);
    const darkFocus = await findKeyboardFocus(page, (focus) => focus.inFooter);

    const screenshotPath = path.join(outputDir, `${width}-home.png`);
    await page.screenshot({ path: screenshotPath, fullPage: true });

    results.push({
      width,
      height,
      responseStatus: response?.status() ?? null,
      navigationError,
      themeVisible: pageState.themeVisible,
      horizontalOverflow: overflow,
      scrollWidth: pageState.scrollWidth,
      clientWidth: pageState.clientWidth,
      headerMode,
      consoleErrors,
      pageErrors,
      duplicateIds,
      search,
      menuState,
      desktop,
      stickyHeader,
      focus: { light: lightFocus, dark: darkFocus },
      screenshot: path.relative(process.cwd(), screenshotPath),
    });

    await context.close();
  }
} finally {
  await browser.close();
}

console.log(JSON.stringify({ baseUrl, executablePath, requireLocalMenu, fallbackContract, results }, null, 2));

const failures = results.filter((result) => {
  const mobileFailure = result.menuState.checked && (
    result.menuState.opened !== true ||
    result.menuState.closedByButton !== true ||
    result.menuState.closedByEscape !== true ||
    result.menuState.bodyScrollLock !== true ||
    result.menuState.focusRestored !== true ||
    result.search.menuCloses !== true ||
    (result.menuState.drilldown.configured && (result.menuState.drilldown.opened !== true || result.menuState.drilldown.backedOut !== true)) ||
    (result.menuState.accordion.configured && (result.menuState.accordion.expanded !== true || result.menuState.accordion.collapsed !== true))
  );
  const desktopFailure = !result.menuState.checked && (
    result.headerMode.desktopNavVisible !== true ||
    result.headerMode.mobileToggleVisible !== false ||
    (result.desktop.configured && (
      result.desktop.opened !== true ||
      result.desktop.panelWithinViewport !== true ||
      result.desktop.closedByEscape !== true ||
      result.desktop.ariaReset !== true ||
      (result.desktop.twoConfigured && result.desktop.exclusive !== true) ||
      result.desktop.searchClosesMega !== true ||
      result.desktop.megaClosesSearch !== true ||
      result.desktop.searchAriaReset !== true
    ))
  );
  const missingConfiguredMenu = requireLocalMenu && !result.menuState.checked && !result.desktop.configured;

  return (
    result.responseStatus === null || result.responseStatus >= 400 || result.navigationError ||
    !result.themeVisible || result.horizontalOverflow || result.consoleErrors.length || result.pageErrors.length ||
    result.duplicateIds.length ||
    !result.headerMode.colorLogoVisible || !result.headerMode.colorLogoLoaded || !result.headerMode.cartVisible ||
    !result.search.opened || !result.search.inputFocused || !result.search.closed || !result.search.focusRestored ||
    mobileFailure || desktopFailure || missingConfiguredMenu ||
    !result.stickyHeader.aboveContent || !result.stickyHeader.announcementScrolledAway ||
    !result.focus.light?.focusVisible || !result.focus.dark?.focusVisible ||
    !fallbackContract.sharedRenderer || !fallbackContract.desktopClass || !fallbackContract.mobileClass
  );
});

if (failures.length) {
  console.error(`Visual UAT failed at: ${failures.map((failure) => failure.width).join(', ')}`);
  process.exitCode = 1;
}
