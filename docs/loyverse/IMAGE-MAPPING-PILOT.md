# Loyverse image mapping — pilot plan and findings

Research date: 24 October 2026. Branch: luna/LOYVERSE-001-inventory-sync-fixes.

## Agreed direction

Image sourcing and product-to-image mapping precede further importer implementation. Use actual product photos from manufacturer or supplier/retailer sources, recording provenance. School-specific costumes await user references. Ghost-mannequin treatment can be considered after the correct source photo is approved; no bulk generation is authorized at this stage. The earlier generated generic example is not a catalogue mapping.

The user accepted grouping suggestions and explicitly selected **publish as out of stock** for products whose stock tracking is disabled in Loyverse. Blocked/ambiguous grouping still needs resolution. No products or images were created, uploaded, published or assigned during this research. Existing plugin remains v0.1.5.

## Pilot

20 product/colour combinations cover 33 Loyverse stock records. 10 strong candidates and 10 possible matches were identified; none is approved. There are 23 image candidates, including alternatives for standard/LB swimsuit variants and cap colours. All 23 URLs returned image content on HTTP HEAD checks. This establishes URL reachability, not final visual identity or reuse permission.

The local review HTML includes thumbnail cards, source links, target source IDs/SKUs, alternatives, reviewer notes, filters and JSON decision export. Source IDs and full catalogue exports remain in ignored local artifacts. Photos are referenced remotely and were not downloaded. Live visual rendering of the local HTML has not been verified.

Local deliverables:

- artifacts/uat/image-mapping/review.html — interactive review sheet.
- artifacts/uat/image-mapping/review.md — Markdown image review.
- artifacts/uat/image-mapping/mappings.json — complete source-ID and candidate-image manifest.
- artifacts/uat/image-mapping/image-url-checks.json — URL/type verification.

## Candidate references

| Mapping | Product / colour | Candidate manufacturer code(s) | Research status | Source |
|---|---|---|---|---|
| IMG-01 | Graphic Pro swimsuit — Blue Cosmo | 011182/690; 011183/690 | Possible match | [Candidate 1](https://catalogue.arenasport.com/fw2026/showproduct.php?cod=011182) · [Candidate 2](https://catalogue.arenasport.com/fw2026/showproduct.php?cod=011183) |
| IMG-02 | Graphic Pro swimsuit — Navy/Water | 011182/780; 011183/780 | Possible match | [Candidate 1](https://catalogue.arenasport.com/fw2026/showproduct.php?cod=011182) · [Candidate 2](https://catalogue.arenasport.com/fw2026/showproduct.php?cod=011183) |
| IMG-03 | Pineapples Tie Back swimsuit — Orange/Multi/Black | 010706/350 | Strong candidate | [Candidate 1](https://aquaswimsupplies.co.uk/products/arena-juicy-fruits-pineapples-tie-back-ladies-swimsuit-orange-multi-black) |
| IMG-04 | Boys Bold Swim Briefs — Black/Turquoise | 011815/508 | Strong candidate | [Candidate 1](https://catalogue.arenasport.com/fw2026/showproduct.php?cod=011815) |
| IMG-05 | Boys Bold Swim Briefs — Neon/Blue | 011815/803 | Possible match | [Candidate 1](https://catalogue.arenasport.com/fw2026/showproduct.php?cod=011815) |
| IMG-06 | Solid Double Cross Back swimsuit — Blue Cosmo | 011595/668 | Possible match | [Candidate 1](https://catalogue.arenasport.com/fw2026/showproduct.php?cod=011595) |
| IMG-07 | One Lacquer one-piece — Navy | 010267/157 | Possible match | [Candidate 1](https://catalogue.arenasport.com/fw2025/showproduct.php?cod=010267) |
| IMG-08 | Fireflow Challenge Back swimsuit — Red | 010154/400 | Possible match | [Candidate 1](https://catalogue.arenasport.com/ss2026/showproduct.php?cod=010154) |
| IMG-09 | The One Plus — Clear/Grey/Blue | 008540/100 | Strong candidate | [Candidate 1](https://catalogue.arenasport.com/fw2026/showproduct.php?cod=008540) |
| IMG-10 | The One Plus — Blue/Blue/Blue | 008540/302 | Strong candidate | [Candidate 1](https://catalogue.arenasport.com/fw2026/showproduct.php?cod=008540) |
| IMG-11 | The One Plus — Smoke/Sage | 008540/500 | Strong candidate | [Candidate 1](https://catalogue.arenasport.com/fw2026/showproduct.php?cod=008540) |
| IMG-12 | Air Sonic — Blue/White | 011004/120 | Possible match | [Candidate 1](https://catalogue.arenasport.com/fw2026/showproduct.php?cod=011004) |
| IMG-13 | Air Sonic — Aqua/Black | 011004/150 | Possible match | [Candidate 1](https://catalogue.arenasport.com/fw2026/showproduct.php?cod=011004) |
| IMG-14 | Air Sonic — Silver/Anthracite | 011004/130 | Possible match | [Candidate 1](https://catalogue.arenasport.com/fw2026/showproduct.php?cod=011004) |
| IMG-15 | Team Mesh Bag — Navy | 002495/710 | Strong candidate | [Candidate 1](https://www.thesportstore.pk/002495-710) |
| IMG-16 | Team Mesh Bag — Royal | 002495/720 | Strong candidate | [Candidate 1](https://kumsalspor.com/team-mesh-team-royal) |
| IMG-17 | One Go Mesh Bag — Royal | 010240/500 | Strong candidate | [Candidate 1](https://catalogue.arenasport.com/fw2026/showproduct.php?cod=010240) |
| IMG-18 | Bubble 3 — Pink | 92395/091 | Strong candidate | [Candidate 1](https://catalogue.arenasport.com/fw2026/showproduct.php?cod=92395) |
| IMG-19 | Bubble 3 — Blue | 92395/070 | Strong candidate | [Candidate 1](https://catalogue.arenasport.com/fw2026/showproduct.php?cod=92395) |
| IMG-20 | Long Hair Cap — Purple | 009275/210; 009275/208 | Possible match | [Candidate 1](https://catalogue.arenasport.com/fw2026/showproduct.php?cod=009275) · [Candidate 2](https://catalogue.arenasport.com/ss2026/showproduct.php?cod=009275) |

## Main ambiguities

- Graphic Pro source names omit manufacturer style and bra/support (LB) designation. Standard 011182 and LB 011183 require comparison.
- Source Air Sonic colours map to manufacturer Air Sonic Mirror 011004; confirm mirrored lenses because the source titles omit Mirror.
- Source One Lacquer Navy does not establish a match to Multi White-Navy 157.
- Long Hair Cap Purple can refer to different purple/pink shades; the 208 and 210 candidates need physical-stock confirmation.
- Team Mesh 002495 and One Go Mesh 010240 are distinct products and must retain separate photos.
- Price/inventory blockers do not disappear when an image is matched.

## Next review gate

User reviews the 20 mappings and exports approved/uncertain/rejected decisions. Resolve remaining model/colour questions using packaging labels or reference photos. Confirm source-image reuse permission, then plan downloads, WordPress media upload and assignment by stable Loyverse IDs. No automatic SKU-only photo assignment. No bulk generation or importer work until this mapping stage is agreed.
