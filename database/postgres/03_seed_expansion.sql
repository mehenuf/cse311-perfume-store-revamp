BEGIN;

-- ============================================================
--  Perfume Store - catalogue expansion
--  Adds 32 products (ids 40-71) across the newer houses:
--  Creed, Rasasi, Rayhaan, Afnan, Versace, Yves Saint Laurent,
--  Giorgio Armani, Paco Rabanne and Prada, plus additions to
--  Lattafa, Armaf, Kilian, Givenchy and Jean Paul Gaultier.
--
--  Run AFTER 01_schema.sql and 02_seed.sql.
--  Safe to run on a live database: it only INSERTs, and it never
--  drops or empties a table.
--
--  PRICING: Bangladeshi Taka, from international USD retail at
--  1 USD = 120 BDT, rounded to the nearest 10 Tk.
--
--  PHOTOGRAPHY: the st_*.jpg files are licensed editorial perfume
--  photographs, not the exact bottles. Replace them per product in
--  the admin panel when you have real product shots.
-- ============================================================

INSERT INTO perfumes
    (id, name, perfume_notes, description, volume, qty, image_path, price, trending, status)
VALUES
    (40, 'Creed Aventus', 'Top: Pineapple, Bergamot, Blackcurrant, Apple. Heart: Birch, Patchouli, Moroccan Jasmine, Rose. Base: Musk, Oakmoss, Ambergris, Vanilla.', 'The most imitated masculine of the century. Smoky birch under bright pineapple, and nothing else smells quite like the real thing.', '100 ml', 9, 'creed_aventus.jpg', 53400.00, 1, 1),
    (41, 'Creed Silver Mountain Water', 'Top: Bergamot, Mandarin. Heart: Green Tea, Blackcurrant. Base: Musk, Sandalwood, Petitgrain.', 'Cold air and green tea over a clean musk. Alpine rather than aquatic, and far more interesting than most fresh scents.', '100 ml', 7, 'creed_silver_mountain_water.png', 48000.00, 0, 1),
    (42, 'Creed Green Irish Tweed', 'Top: Lemon Verbena, Peppermint. Heart: Violet Leaf, Iris. Base: Sandalwood, Ambergris.', 'Violet leaf and iris over sandalwood. A 1985 benchmark that half the fresh-fougere shelf has been chasing ever since.', '100 ml', 6, 'creed_green_irish_tweed.jpg', 46800.00, 0, 1),
    (43, 'Creed Viking', 'Top: Bergamot, Pink Pepper, Lavender. Heart: Peppermint, Rose, Cardamom. Base: Sandalwood, Vetiver, Patchouli.', 'Peppermint and pink pepper over dry sandalwood. Sharper and more modern than the house classics.', '100 ml', 5, 'creed_viking.jpg', 49800.00, 0, 1),
    (44, 'Rasasi Hawas Ice', 'Top: Bergamot, Apple, Lime. Heart: Ambergris, Cardamom, Rosemary. Base: Musk, Amberwood, Cedar.', 'The cooler cousin of Hawas. Same salty ambergris core, with citrus and lime laid over the top.', '100 ml', 34, 'rasasi_hawas_ice.jpg', 7200.00, 1, 1),
    (45, 'Rasasi La Yuqawam Pour Homme', 'Top: Grapefruit, Cardamom, Nutmeg. Heart: Leather, Cypriol, Cinnamon. Base: Leather, Amber, Musk.', 'Spiced leather with real depth. The name means irresistible, and for once the marketing is close to honest.', '75 ml', 18, 'st_amber_bottles_bokeh.jpg', 11400.00, 0, 1),
    (46, 'Rasasi Daarej Pour Homme', 'Top: Bergamot, Lavender, Pineapple. Heart: Cinnamon, Geranium. Base: Vanilla, Sandalwood, Musk.', 'A warm, sweet fougere that punches well above its price. Easy to wear from the office into the evening.', '100 ml', 40, 'rasasi_daarej_pour_homme.jpg', 5760.00, 0, 1),
    (47, 'Rayhaan Bariq', 'Top: Pineapple, Bergamot. Heart: Birch, Jasmine. Base: Musk, Ambergris, Vanilla.', 'Smoky pineapple in the Aventus mould, at a fraction of the cost. Loud, cheerful and very effective.', '100 ml', 45, 'rayhaan_bariq.jpg', 4560.00, 1, 1),
    (48, 'Rayhaan Roberto Rayhaan Noir', 'Top: Saffron, Cardamom. Heart: Rose, Oud. Base: Amber, Musk, Vanilla.', 'Saffron and rose over a soft oud. Gulf opulence without the weight that usually comes with it.', '100 ml', 30, 'rayhaan_roberto_rayhaan_noir.png', 5400.00, 0, 1),
    (49, 'Rayhaan Ghala Zayed Luxury Gold', 'Top: Bergamot, Saffron. Heart: Rose, Patchouli, Oud. Base: Amber, Musk, Sandalwood.', 'Rich, golden and unapologetically sweet. Built for cold evenings and long nights.', '100 ml', 26, 'rayhaan_ghala_zayed_luxury_gold.png', 6240.00, 0, 1),
    (50, 'Afnan 9PM', 'Top: Apple, Cinnamon, Lavender. Heart: Vanilla, Tonka Bean, Orange Blossom. Base: Amberwood, Praline, Patchouli.', 'Apple and cinnamon over praline. The bottle that made Afnan a household name outside the Gulf.', '100 ml', 55, 'afnan_9pm.png', 4800.00, 1, 1),
    (51, 'Afnan Supremacy Not Only Intense', 'Top: Pineapple, Bergamot, Blackcurrant. Heart: Birch, Jasmine, Rose. Base: Ambergris, Musk, Vanilla.', 'Another take on the smoky pineapple accord, pushed louder and sweeter. Enormous projection.', '100 ml', 42, 'afnan_supremacy_not_only_intesne.jpg', 5400.00, 0, 1),
    (52, 'Afnan Turathi Blue', 'Top: Bergamot, Grapefruit. Heart: Lavender, Geranium. Base: Amberwood, Musk, Cedar.', 'A clean blue fragrance with a woody-amber spine. Straightforward, and very easy to reach for.', '90 ml', 38, 'afnan_turathi_blue.png', 4320.00, 0, 1),
    (53, 'Versace Eros', 'Top: Mint, Green Apple, Lemon. Heart: Tonka Bean, Ambroxan, Geranium. Base: Vanilla, Cedar, Vetiver, Oakmoss.', 'Mint and green apple over vanilla and tonka. Sweet, bright, and impossible to mistake for anything else.', '100 ml', 32, 'st_turquoise_flacon.jpg', 13200.00, 1, 1),
    (54, 'Versace Dylan Blue', 'Top: Bergamot, Grapefruit, Fig Leaf. Heart: Violet Leaf, Papyrus, Patchouli. Base: Musk, Tonka Bean, Saffron.', 'Fig leaf and grapefruit with a mineral edge. The most wearable thing the house has made in years.', '100 ml', 28, 'versace_dylan_blue.jpeg', 12600.00, 0, 1),
    (55, 'Versace Dylan Turquoise', 'Top: Lemon, Primofiore, Guava. Heart: Jasmine, Freesia. Base: Musk, Sandalwood, Amberwood.', 'Guava and citrus over clean musk. Summer in a bottle, and light enough to wear all day.', '100 ml', 30, 'versace_dylan_turquoise.jpg', 12000.00, 0, 1),
    (56, 'Yves Saint Laurent Y Eau De Parfum', 'Top: Apple, Ginger, Bergamot. Heart: Sage, Juniper, Geranium. Base: Amberwood, Tonka Bean, Cedar.', 'Crisp apple and sage over amberwood. Sharp, modern and cut for a suit.', '100 ml', 24, 'yves_saint_laurent_y_eau_de_parfum.png', 16200.00, 0, 1),
    (57, 'Yves Saint Laurent La Nuit De L Homme', 'Top: Cardamom. Heart: Lavender, Cedar. Base: Vetiver, Coumarin.', 'Cardamom and cedar, and almost nothing else. Restraint is the whole point, and it works.', '100 ml', 26, 'yves_saint_laurent_la_nuit_de_l_homme.jpg', 15600.00, 0, 1),
    (58, 'Yves Saint Laurent Black Opium', 'Top: Pear, Pink Pepper, Orange Blossom. Heart: Coffee, Jasmine, Licorice. Base: Vanilla, Patchouli, Cedar.', 'Black coffee and vanilla with a jasmine lift. The bottle that defined a decade of feminine gourmands.', '90 ml', 29, 'st_noir_sparkle.jpg', 16800.00, 1, 1),
    (59, 'Giorgio Armani Acqua Di Gio Profumo', 'Top: Bergamot, Marine Notes. Heart: Sage, Rosemary, Geranium. Base: Incense, Patchouli.', 'The aquatic taken somewhere darker, with incense under the sea air. The version worth owning.', '75 ml', 22, 'st_minimal_white.jpg', 15000.00, 0, 1),
    (60, 'Giorgio Armani Code Parfum', 'Top: Green Apple, Bergamot. Heart: Orange Blossom, Tonka Bean. Base: Tobacco, Leather, Amber.', 'Tobacco and tonka under bright apple. Warm, tailored, and made for a room with low lighting.', '100 ml', 20, 'giorgio_armani_code_parfum.png', 14400.00, 0, 1),
    (61, 'Paco Rabanne 1 Million', 'Top: Grapefruit, Mint, Blood Mandarin. Heart: Rose, Cinnamon, Spices. Base: Leather, Amber, Patchouli.', 'Cinnamon and leather in a gold bar. Subtlety was never on the brief, and it sells anyway.', '100 ml', 35, 'paco_rabanne_1_million.jpg', 12600.00, 1, 1),
    (62, 'Paco Rabanne Invictus', 'Top: Grapefruit, Marine Accord. Heart: Bay Leaf, Jasmine. Base: Ambergris, Guaiac Wood, Patchouli.', 'Grapefruit and salt over guaiac wood. Built for the gym bag and surprisingly good at it.', '100 ml', 33, 'paco_rabanne_invictus.jpg', 12000.00, 0, 1),
    (63, 'Prada Luna Rossa Carbon', 'Top: Bergamot, Lavender Absolute. Heart: Pepper, Coal Accord. Base: Ambroxan, Patchouli.', 'A metallic coal accord over lavender. Cold, clean and architectural, which is very Prada.', '100 ml', 21, 'st_black_monolith.jpg', 13800.00, 1, 1),
    (64, 'Prada L Homme', 'Top: Neroli, Black Pepper, Cardamom. Heart: Iris, Violet, Geranium. Base: Amber, Patchouli, Cedar.', 'Powdery iris and neroli with a soft amber base. Quiet, refined, and completely self-assured.', '100 ml', 19, 'prada_l_homme.jpg', 13200.00, 0, 1),
    (65, 'Lattafa Yara', 'Top: Orchid, Heliotrope. Heart: Tuberose, Gourmand Notes. Base: Vanilla, Musk, Sandalwood.', 'Creamy tuberose and vanilla. Sweet, soft and everywhere for good reason.', '100 ml', 60, 'lattafa_yara.jpeg', 3360.00, 0, 1),
    (66, 'Lattafa Fakhar Black', 'Top: Bergamot, Lavender, Apple. Heart: Cinnamon, Geranium. Base: Vanilla, Tonka Bean, Amber.', 'A dark, sweet fougere with real staying power. One of the best value bottles on the shelf.', '100 ml', 52, 'lattafa_fakhar_black.jpg', 3600.00, 0, 1),
    (67, 'Armaf Club De Nuit Sillage', 'Top: Bergamot, Grapefruit, Cardamom. Heart: Rose, Jasmine, Cypress. Base: Amber, Musk, Vetiver.', 'Brighter and airier than the Intense, with the same enormous trail behind it.', '105 ml', 36, 'armaf_club_de_nuit_sillage.jpg', 6000.00, 0, 1),
    (68, 'Armaf Ventana Pour Homme', 'Top: Bergamot, Pineapple, Apple. Heart: Birch, Jasmine. Base: Vanilla, Musk, Ambergris.', 'Fruity and smoky in equal measure. An easy everyday bottle that never feels cheap.', '100 ml', 30, 'armaf_ventana_pour_homme.png', 5040.00, 0, 1),
    (69, 'Kilian Black Phantom', 'Top: Rum, Coffee. Heart: Caramel, Sugar Cane, Almond. Base: Sandalwood, Dark Chocolate, Patchouli.', 'Rum, coffee and dark chocolate. A pirate skull on the bottle, and a genuinely serious gourmand inside.', '50 ml', 7, 'kilian_black_phantom.webp', 32400.00, 0, 1),
    (70, 'Givenchy Gentleman Society', 'Top: Vetiver, Bergamot. Heart: Iris, Cardamom, Sage. Base: Vanilla, Patchouli, Cedar.', 'Iris and vetiver with a vanilla softness underneath. The most grown-up Gentleman yet.', '100 ml', 23, 'givenchy_gentleman_society.png', 14400.00, 0, 1),
    (71, 'Jean Paul Gaultier Ultra Male', 'Top: Pear, Lavender, Mint. Heart: Cinnamon, Cardamom, Clary Sage. Base: Vanilla, Amber, Patchouli.', 'Pear and vanilla turned all the way up. Nobody has ever accused this one of being subtle.', '125 ml', 27, 'jean_paul_gaultier_ultra_male.jpg', 13200.00, 0, 1);

-- Keep the identity sequence ahead of the ids inserted above.
SELECT setval(pg_get_serial_sequence('perfumes', 'id'), (SELECT MAX(id) FROM perfumes));

COMMIT;
