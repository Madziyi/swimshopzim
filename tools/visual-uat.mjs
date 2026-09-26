import fs from 'node:fs/promises';
import fsSync from 'node:fs';
import path from 'node:path';
import process from 'node:process';
import { chromium } from 'playwright';

const baseUrl = process.env.SSZ_LOCAL_URL ?? 'http://swimshop-zimbabwe.local/';
const widths = [360, 390, 430, 768, 1024, 1280, 1440, 1920];
const outputDir = path.resolve('artifacts/uat');

const browserCandidates = [
  process.env.SSZ_BROWSER_PATH,
  process.env.ProgramFiles && path.join(process.env.ProgramFiles, 'Google', 'Chrome', 'Application', 'chrome.exe'),
  process.env['ProgramFiles(x86)'] && path.join(process.env['ProgramFiles(x86)'], 'Google', 'Chrome', 'Application', 'chrome.exe'),
  process.env.ProgramFiles && path.join(process.env.ProgramFiles, 'Microsoft', 'Edge', 'Application', 'msedge.exe'),
  process.env['ProgramFiles(x86)'] && path.join(process.env['ProgramFiles(x86)'], 'Microsoft', 'Edge', 'Application', 'msedge.exe'),
].filter(Boolean);

const executablePath = browserCandidates.find((candidate) => {
  return fsSync.existsSync(candidate);
});

if (!executablePath) {
  throw new Error('No Chrome or Edge executable found. Set SSZ_BROWSER_PATH to a local browser executable.');
}

await fs.mkdir(outputDir, { recursive: true });

const findKeyboardFocus = async (page, predicate) => {
  await page.mouse.click(1, 1);
  for (let attempt = 0; attempt < 80; attempt += 1) {
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
      const header = document.querySelector('[data-site-header]');
      return {
        innerWidth: window.innerWidth,
        clientWidth: root?.clientWidth ?? 0,
        scrollWidth: Math.max(root?.scrollWidth ?? 0, body?.scrollWidth ?? 0),
        themeVisible: Boolean(document.querySelector('.ssz-site-shell, .ssz-header[data-site-header], .ssz-hero')),
        headerPresent: Boolean(header),
      };
    });

    const overflow = pageState.scrollWidth > pageState.clientWidth + 2;
    const search = { opened: false, closed: false, inputUsable: false };
    const searchToggle = page.locator('[data-search-toggle]');
    if (await searchToggle.count()) {
      await searchToggle.click();
      const panel = page.locator('[data-search-panel]');
      const input = page.locator('[data-search-panel] input[type="search"]');
      search.opened = await panel.isVisible();
      search.inputUsable = (await input.count()) > 0 && await input.isVisible() && await input.isEnabled();
      await page.locator('[data-search-close]').click();
      search.closed = !(await panel.isVisible());
    }

    const mobileMenu = { checked: width <= 1024, opened: null, closed: null };
    const menuToggle = page.locator('[data-menu-toggle]');
    if (mobileMenu.checked && await menuToggle.count() && await menuToggle.isVisible()) {
      const mobileNav = page.locator('[data-mobile-nav]');
      await menuToggle.click();
      mobileMenu.opened = await mobileNav.isVisible();
      await page.keyboard.press('Escape');
      mobileMenu.closed = !(await mobileNav.isVisible());
    }

    await page.evaluate(() => window.scrollTo(0, 500));
    const stickyHeader = await page.evaluate(() => {
      const header = document.querySelector('[data-site-header]');
      if (!header) return { present: false, aboveContent: false };
      const rect = header.getBoundingClientRect();
      const elements = document.elementsFromPoint(rect.left + Math.max(4, rect.width / 2), Math.max(4, rect.top + 4));
      return {
        present: true,
        top: rect.top,
        aboveContent: rect.top <= 1 && rect.bottom > rect.top && elements.some((element) => element.closest?.('[data-site-header]')),
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
      consoleErrors,
      pageErrors,
      search,
      mobileMenu,
      stickyHeader,
      focus: { light: lightFocus, dark: darkFocus },
      screenshot: path.relative(process.cwd(), screenshotPath),
    });

    await context.close();
  }
} finally {
  await browser.close();
}

console.log(JSON.stringify({ baseUrl, executablePath, results }, null, 2));

const failures = results.filter((result) => (
  result.responseStatus === null || result.responseStatus >= 400 || result.navigationError ||
  !result.themeVisible || result.horizontalOverflow || result.consoleErrors.length || result.pageErrors.length ||
  !result.search.opened || !result.search.closed || !result.search.inputUsable ||
  (result.mobileMenu.checked && (result.mobileMenu.opened !== true || result.mobileMenu.closed !== true)) ||
  !result.stickyHeader.aboveContent || !result.focus.light?.focusVisible || !result.focus.dark?.focusVisible
));

if (failures.length) {
  console.error(`Visual UAT failed at: ${failures.map((failure) => failure.width).join(', ')}`);
  process.exitCode = 1;
}
