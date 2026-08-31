-- ============================================================
--  Perfume Store - MySQL / MariaDB seed data
--  Run AFTER mysql/01_schema.sql
-- ============================================================
SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- ============================================================
--  Perfume Store - seed data (dialect-neutral core)
--  Shared verbatim by the MySQL and PostgreSQL seed scripts.
--
--  PRICING: every price is Bangladeshi Taka (Tk), derived from the
--  product's international USD retail price at 1 USD = 120 BDT and
--  rounded to the nearest 10 Tk. The storefront prints "Tk. <price>".
--
--  Rows are inserted with explicit primary keys so that the foreign
--  keys below (cart, orders, order_item) resolve deterministically.
--  Each dialect's script re-syncs its id generator afterwards.
-- ============================================================

-- Clear in child -> parent order so no foreign key is ever violated.
DELETE FROM order_item;
DELETE FROM orders;
DELETE FROM cart;
DELETE FROM perfumes;
DELETE FROM customer;

-- ------------------------------------------------------------
-- customer
-- NOTE: passwords are stored in plain text because functions/authcode.php
-- authenticates with "WHERE username = ? AND password = ?". See the
-- README - hashing these is the single most important follow-up fix.
-- admin_check = 1 grants access to /admin.
-- ------------------------------------------------------------
INSERT INTO customer (id, username, password, name, email, contacts, address, dob, admin_check) VALUES
    (1, 'mehenuf',  'admin123',   'Mehenuf Hossain Bhuiyan', 'mehenuf@gmail.com',      '+8801700000001', 'House 12, Road 5, Dhanmondi, Dhaka 1205',       '1999-04-17', 1),
    (2, 'shopadmin','admin123',   'Store Administrator',     'admin@perfumestore.test','+8801700000002', 'Level 4, Bashundhara City, Panthapath, Dhaka',  '1995-11-02', 1),
    (3, 'arif',     'arif1234',   'Arif Rahman',             'arif.rahman@example.com','+8801711111111', 'Flat 3B, Shahjadpur, Gulshan, Dhaka 1212',      '2000-01-23', 0),
    (4, 'nusrat',   'nusrat1234', 'Nusrat Jahan',            'nusrat.j@example.com',   '+8801722222222', '54 Zindabazar, Sylhet 3100',                    '2001-07-09', 0),
    (5, 'tanvir',   'tanvir1234', 'Tanvir Ahmed',            'tanvir.a@example.com',   '+8801733333333', '19 Agrabad C/A, Chattogram 4100',               '1997-03-30', 0),
    (6, 'sadia',    'sadia1234',  'Sadia Islam',             'sadia.islam@example.com','+8801744444444', 'Zone 6, Uttara Sector 7, Dhaka 1230',           '2002-12-14', 0);

-- ------------------------------------------------------------
-- perfumes
-- status   1 = published (visible on the storefront)
-- trending 1 = featured in the home-page carousel
-- Product names deliberately begin with the brand, because the brand
-- pages filter with  name LIKE '%dior%', '%chanel%', '%Tom%Ford%', etc.
-- image_path values match the files already present in /images.
-- ------------------------------------------------------------
INSERT INTO perfumes (id, name, perfume_notes, description, volume, qty, image_path, price, trending, status) VALUES
    (1,  'Dior Sauvage',                         'Top: Calabrian Bergamot, Pepper. Heart: Sichuan Pepper, Lavender, Vetiver. Base: Ambroxan, Cedar, Labdanum.',                        'A radically fresh composition built around Ambroxan, drawn from prized ambergris. Raw and noble at once, it is the scent of wide open spaces under a blue-white sky.',      '100 ml', 40, 'dior_sauvage.jpg',                          13800.00, 1, 1),
    (2,  'Dior Sauvage Elixir',                  'Top: Cinnamon, Nutmeg, Cardamom, Grapefruit. Heart: Lavender Essence. Base: Licorice, Sandalwood, Amber, Haitian Vetiver.',           'An overdose of raw materials. Spice, lavender essence and a liquorice accord fold into a concentrated trail that carries from evening into the next morning.',              '60 ml',  22, 'dior_sauvage_elixir.jpg',                   22200.00, 1, 1),
    (3,  'Dior Homme Intense',                   'Top: Lavender. Heart: Iris Pallida, Ambrette, Pear. Base: Virginia Cedar, Vetiver.',                                                  'Iris at full power, powdery and velvety, wrapped in warm woods. The most refined and romantic reading of the Dior Homme signature.',                                        '100 ml', 18, 'dior_homme_intense.jpg',                    16800.00, 1, 1),
    (4,  'Dior Homme Original',                  'Top: Bergamot, Pink Pepper. Heart: Iris, Cocoa, Leather. Base: Patchouli, Vetiver, Musk.',                                            'The original 2005 masterpiece. Cool metallic iris over a suede and cocoa base that made an entire generation of masculine perfumery rethink itself.',                       '100 ml', 12, 'dior_diorHomme_og.jpg',                     15600.00, 0, 1),
    (5,  'Dior Homme Sport',                     'Top: Bergamot, Ginger, Elemi. Heart: Sichuan Pepper, Nutmeg. Base: Amberwood, Cedar, Vetiver.',                                       'Bright citrus and ginger over a clean woody-amber drydown. The easy, daylight member of the Dior Homme family.',                                                            '125 ml', 26, 'dior_homme_sport.jpg',                      14400.00, 0, 1),
    (6,  'Dior Fahrenheit',                      'Top: Mandarin, Nutmeg Flower, Hawthorn. Heart: Violet, Nutmeg, Cedar. Base: Leather, Vetiver, Tonka Bean.',                           'The legendary petrol-and-violet accord. Nothing before it smelled like this and very little since. A genuine landmark in perfumery.',                                       '100 ml', 15, 'dior_fahrenheit.jpg',                       15000.00, 0, 1),
    (7,  'Dior Eau Sauvage Fraiche',             'Top: Sicilian Lemon, Basil, Bergamot. Heart: Jasmine, Rosemary. Base: Vetiver, Oakmoss, Musk.',                                       'A cooler, greener reworking of the 1966 classic. Hesperidic, herbal and effortlessly elegant in warm weather.',                                                             '100 ml', 30, 'dior_eau_fraiche.jpg',                      12600.00, 0, 1),
    (8,  'Chanel Bleu De Chanel',                'Top: Grapefruit, Lemon, Mint, Pink Pepper. Heart: Ginger, Nutmeg, Jasmine. Base: Incense, Vetiver, Cedar, Sandalwood.',               'A woody aromatic in a deep blue bottle. Fresh citrus opening, smoky incense heart, dry cedar close. The modern reference for a versatile signature.',                        '100 ml', 35, 'bleu_de_chanel.jpg',                        18000.00, 1, 1),
    (9,  'Chanel Allure Homme',                  'Top: Sicilian Mandarin, Bergamot. Heart: Cedar, Nutmeg, Jasmine. Base: Tonka Bean, Vanilla, Vetiver.',                                'Warm, softly sweet and impeccably balanced. A tonka and vanilla drydown that stays close to the skin and reads expensive.',                                                 '100 ml', 20, 'chanel_allure_homme.jpg',                   16200.00, 0, 1),
    (10, 'Chanel Allure Homme Sport',            'Top: Orange, Aldehydes, Mandarin. Heart: Pepper, Neroli, Cedar. Base: Tonka Bean, White Musk, Amber, Vanilla.',                       'Aldehydic citrus with a clean pepper-and-cedar spine. Crisp, sporty and endlessly wearable in daylight.',                                                                   '100 ml', 28, 'chanel_allure_homme_sport.jpg',             15600.00, 1, 1),
    (11, 'Chanel Coco Mademoiselle',             'Top: Orange, Bergamot, Grapefruit. Heart: Turkish Rose, Jasmine, Litchi. Base: Patchouli, Vetiver, White Musk, Vanilla.',             'A bold oriental for the modern woman. Sparkling orange over an addictive rose-patchouli heart that has defined a generation.',                                              '100 ml', 24, 'chanel_coco_mademoiselle.jpg',              19200.00, 1, 1),
    (12, 'Chanel No 5',                          'Top: Aldehydes, Ylang-Ylang, Neroli, Bergamot. Heart: Iris, Jasmine, Rose, Lily of the Valley. Base: Sandalwood, Vanilla, Amber.',    'The most famous perfume ever created. The 1921 aldehydic floral by Ernest Beaux remains the benchmark against which all others are measured.',                              '100 ml', 16, 'chanel_no5.jpg',                            22200.00, 0, 1),
    (13, 'Tom Ford Black Orchid',                'Top: Truffle, Ylang-Ylang, Blackcurrant, Bergamot. Heart: Orchid, Spices, Lotus Wood. Base: Patchouli, Vanilla, Incense, Sandalwood.','Dark, luxurious and faintly scandalous. Black truffle and orchid over a resinous vanilla base. Unmistakable in any room.',                                                  '100 ml', 18, 'tomford_black_orchid.jpg',                  24600.00, 1, 1),
    (14, 'Tom Ford Tuscan Leather',              'Top: Raspberry, Saffron, Thyme. Heart: Leather, Olibanum, Jasmine. Base: Suede, Amber, Woody Notes.',                                 'Raw suede and raspberry. A brutal, magnetic leather that reads like the inside of a new car and a boxing glove at once.',                                                   '50 ml',  10, 'tomford_tuscan_leather.jpg',                30000.00, 1, 1),
    (15, 'Tom Ford Ombre Noir',                  'Top: Cardamom, Black Pepper. Heart: Rose, Patchouli, Leather. Base: Oud, Amber, Vanilla, Tonka.',                                     'Smoky oud and leather bound with a dark rose. Opulent, nocturnal and built for cold weather.',                                                                              '100 ml',  9, 'tomford_ombre_noir.jpg',                    27600.00, 0, 1),
    (16, 'Tom Ford Neroli Portofino',            'Top: Bergamot, Mandarin, Lemon, Neroli. Heart: Orange Blossom, Jasmine, Lavender. Base: Amber, Angelica, Ambrette.',                  'The Italian Riviera in a bottle. Bright neroli and citrus, breezy and immaculate. Radiant in heat.',                                                                        '100 ml', 14, 'tomoford_neroli_portofino.jpg',             27000.00, 0, 1),
    (17, 'Tom Ford Costa Azzurra Acqua',         'Top: Sea Notes, Lemon, Cypress. Heart: Driftwood, Seaweed, Vetiver. Base: Oakmoss, Ambrette, Musk.',                                  'Salt air, driftwood and Mediterranean scrub. An aquatic with real texture rather than the usual synthetic freshness.',                                                      '100 ml', 13, 'tomford_costa_azzura_acqua.jpg',            25200.00, 0, 1),
    (18, 'Hugo Boss Bottled',                    'Top: Apple, Plum, Bergamot, Lemon. Heart: Cinnamon, Mahogany, Carnation. Base: Vanilla, Sandalwood, Cedar, Olive Wood.',              'The apple-and-cinnamon office classic since 1998. Smart, warm and completely inoffensive in the best possible way.',                                                        '100 ml', 45, 'hugoboss_bossbottled.jpg',                   7920.00, 1, 1),
    (19, 'Hugo Boss Bottled Night',              'Top: Lavender, Birch. Heart: African Violet Leaf, Jasmine. Base: Musk, Louro Amarelo Wood.',                                          'Sharper and drier than the original. Violet leaf over woody musk, cut for evenings and cooler air.',                                                                        '100 ml', 32, 'hugoboss_bossbottlednight.jpg',              8400.00, 0, 1),
    (20, 'Hugo Boss Bottled Pacific',            'Top: Grapefruit, Bergamot. Heart: Marine Accord, Geranium. Base: Cedarwood, Amberwood, Musk.',                                        'A saline grapefruit take on the Bottled DNA. Light, clean and made for long summer days.',                                                                                  '100 ml', 29, 'hugoboss_bossbottledpacific.jpg',            8160.00, 0, 1),
    (21, 'Hugo Boss Hugo Man',                   'Top: Green Apple, Grapefruit, Mint. Heart: Geranium, Jasmine, Carnation. Base: Patchouli, Fir, Cedar, Vetiver.',                      'Crisp green apple and mint in the iconic flask bottle. A 1995 fresh aromatic that still feels alert and young.',                                                            '125 ml', 38, 'hugoboss_hugo.jpg',                          6720.00, 0, 1),
    (22, 'Lattafa Khamrah',                      'Top: Cinnamon, Nutmeg, Bergamot. Heart: Dates, Praline, Tuberose, Mahonial. Base: Vanilla, Tonka Bean, Amber, Benzoin, Myrrh.',       'Spiced dates and boozy praline over a thick vanilla base. The gourmand that put Lattafa on the global map.',                                                                '100 ml', 60, 'lattafa_khamrah.jpg',                        4200.00, 1, 1),
    (23, 'Lattafa Asad',                         'Top: Blue Ginger, Pineapple, Black Pepper. Heart: Lavender, Vetiver, Blackcurrant. Base: Tonka Bean, Vanilla, Cedarwood.',            'A confident, spicy-sweet powerhouse with enormous projection for the money. Pineapple and tonka over dry wood.',                                                            '100 ml', 55, 'lattafa_asad.jpg',                           3840.00, 1, 1),
    (24, 'Lattafa Ajayeb Dubai',                 'Top: Saffron, Bergamot, Pink Pepper. Heart: Rose, Jasmine, Orris. Base: Amberwood, Oud, Musk, Patchouli.',                            'Saffron and rose in a polished Middle Eastern amber-wood frame. Rich, warm and generous on the skin.',                                                                      '100 ml', 42, 'lattafa_ajayeb_dubai.jpg',                   4560.00, 0, 1),
    (25, 'Lattafa Suqraat',                      'Top: Bergamot, Green Notes, Cardamom. Heart: Rose, Geranium, Saffron. Base: Oud, Amber, Musk, Vanilla.',                              'A smooth oud-rose blend that stays wearable rather than heavy. Excellent value in the niche-inspired space.',                                                               '100 ml', 36, 'lattafa_suqraat.jpg',                        4080.00, 0, 1),
    (26, 'Mancera Red Tobacco',                  'Top: Saffron, Cinnamon, Nutmeg, Orange. Heart: Tobacco, Oud, Rose. Base: Vanilla, Amber, Musk, Tonka.',                               'Sweet spiced tobacco with monstrous longevity. One of the loudest and most loved fragrances Mancera has made.',                                                             '120 ml', 17, 'mancera_redtobacco.jpg',                    19800.00, 1, 1),
    (27, 'Mancera Cedrat Boise',                 'Top: Sicilian Lemon, Bergamot, Blackcurrant. Heart: Sandalwood, Patchouli, Leather. Base: Vanilla, White Musk, Amber, Oud.',          'Bright citrus over a sweet woody-amber core. A crowd-pleasing signature that works in almost any setting.',                                                                 '120 ml', 21, 'mancera_cedratboise.jpg',                   18240.00, 1, 1),
    (28, 'Mancera Hindu Kush',                   'Top: Cannabis Accord, Bergamot, Lemon. Heart: Rose, Saffron, Jasmine. Base: Oud, Patchouli, Amber, Vanilla.',                         'A green herbal cannabis accord folded into oud and amber. Unconventional, hypnotic and instantly recognisable.',                                                            '120 ml', 11, 'mancera_hindukush.jpg',                     20640.00, 0, 1),
    (29, 'Mancera Instant Crush',                'Top: Bergamot, Green Notes, Cardamom. Heart: Rose, Jasmine, Osmanthus. Base: Oud, Amber, Vanilla, Musk.',                             'Fruity florals lifted by a soft oud base. Polished, modern and comfortably unisex.',                                                                                        '120 ml', 14, 'mancera_instantcrush.jpg',                  18960.00, 0, 1),
    (30, 'Mancera Lemon Mint',                   'Top: Lemon, Mint, Bergamot, Grapefruit. Heart: Jasmine, Rose, Green Notes. Base: White Musk, Amber, Cedar, Vanilla.',                 'Ice-cold lemon and mint over a soft musky base. The most refreshing thing in the Mancera line-up.',                                                                         '120 ml', 19, 'mancera_lemonmint.jpg',                     17640.00, 0, 1),
    (31, 'Armaf Club De Nuit Intense Man',       'Top: Pineapple, Blackcurrant, Lemon, Apple, Bergamot. Heart: Birch, Jasmine, Rose. Base: Vanilla, Musk, Ambergris, Patchouli.',       'The famous smoky pineapple opening over birch tar. Enormous performance at a fraction of its inspiration price.',                                                           '105 ml', 50, 'armaf_cdnim.jpg',                            5400.00, 1, 1),
    (32, 'Rasasi Hawas For Him',                 'Top: Apple, Cinnamon, Bergamot. Heart: Ambergris, Jasmine, Cardamom. Base: Musk, Driftwood, Oakmoss, Cedar.',                         'Fresh aquatic apple with a salty ambergris core. A reliable compliment machine that suits nearly every occasion.',                                                          '100 ml', 44, 'rasasi_hawas.jpg',                           6960.00, 1, 1),
    (33, 'Givenchy Gentleman',                   'Top: Pear, Cardamom. Heart: Iris, Lavender, Leather. Base: Patchouli, Vanilla, Black Vanilla Husk.',                                  'Pear and iris over a soft leather base. A quietly self-assured modern take on the 1975 original.',                                                                          '100 ml', 23, 'givenchy_gentlemen.jpg',                    13560.00, 0, 1),
    (34, 'Jean Paul Gaultier Le Beau Le Parfum', 'Top: Bergamot, Coconut Wood. Heart: Ginger, Cardamom. Base: Tonka Bean, Sandalwood, Amberwood.',                                      'Creamy coconut wood and warm tonka. Sun-drenched, smooth and unapologetically flirtatious.',                                                                                '125 ml', 27, 'jpg_le_bleu_le_parfum.jpg',                 12960.00, 0, 1),
    (35, 'Kilian Angels Share',                  'Top: Cognac, Cinnamon. Heart: Tonka Bean, Praline, Oak. Base: Vanilla, Sandalwood, Tonka Absolute.',                                  'Cognac aged in oak, cinnamon and praline. A gourmand of genuine craft, named for the share of spirit lost to evaporation.',                                                 '50 ml',   8, 'kilian_angels_share.jpg',                   31440.00, 1, 1),
    (36, 'Salvatore Ferragamo Black',            'Top: Bergamot, Cardamom, Violet Leaf. Heart: Black Pepper, Nutmeg, Cedar. Base: Tonka Bean, Vanilla, Patchouli, Musk.',               'A dark, spicy-sweet evening wear with a smooth tonka finish. Understated Italian tailoring in fragrance form.',                                                             '100 ml', 25, 'salvatore_ferregamo_ferregamo_black.jpg',    9240.00, 0, 1),
    (37, 'Luxodor Loyal Agar',                   'Top: Saffron, Bergamot, Cardamom. Heart: Agarwood, Rose, Patchouli. Base: Amber, Sandalwood, Musk, Vanilla.',                         'A deep agarwood composition with a saffron-rose opening. Traditional Middle Eastern character with a modern polish.',                                                       '100 ml', 20, 'luxodor_loyal_agar.jpg',                     7440.00, 0, 1),
    (38, 'Spectre Ghost',                        'Top: Bergamot, Pink Pepper, Elemi. Heart: Incense, Violet, Cypress. Base: Vetiver, Ambroxan, Grey Musk.',                             'Cool incense and grey musk with a translucent, almost weightless trail. Quiet, cerebral and slightly unnerving.',                                                           '100 ml', 12, 'spectre_ghost.jpg',                          8760.00, 0, 1),
    (39, 'Burnt Hair',                           'Top: Singed Keratin, Smoke, Ozone. Heart: Charred Wood, Petrichor. Base: Ash, Ambroxan, Mineral Musk.',                               'A novelty scorched-smoke accord kept in the archive but unpublished. Far more wearable than the name suggests.',                                                            '100 ml',  5, 'burnt_hair.jpg',                            12000.00, 0, 0);

-- ------------------------------------------------------------
-- cart : live baskets. UNIQUE (user_id, perfume_id) is respected -
-- no customer holds the same perfume on two rows.
-- ------------------------------------------------------------
INSERT INTO cart (id, user_id, perfume_id, perfume_qty) VALUES
    (1, 3,  8, 1),
    (2, 3, 22, 2),
    (3, 4, 11, 1),
    (4, 5, 31, 3),
    (5, 6, 13, 1),
    (6, 6, 35, 1);

-- ------------------------------------------------------------
-- orders : headers. tracking_no follows the format built in
-- functions/placeorder.php -> "TRK" + random digits + first 4
-- characters of the username.
-- status 0=Processing 1=Completed 2=Shipped 3=Delivered 4=Cancelled
-- total_price equals the sum of its order_item lines (see below).
-- ------------------------------------------------------------
INSERT INTO orders (id, tracking_no, user_id, name, email, contacts, address, zipcode, total_price, payment_mode, payment_id, status) VALUES
    (1, 'TRK482913756arif',   3, 'Arif Rahman',  'arif.rahman@example.com', '+8801711111111', 'Flat 3B, Shahjadpur, Gulshan, Dhaka',   '1212', 22200.00, 'COD', '7461920385', 3),
    (2, 'TRK905172634nusr',   4, 'Nusrat Jahan', 'nusrat.j@example.com',    '+8801722222222', '54 Zindabazar, Sylhet',                 '3100', 19200.00, 'COD', '3095817264', 2),
    (3, 'TRK173940582tanv',   5, 'Tanvir Ahmed', 'tanvir.a@example.com',    '+8801733333333', '19 Agrabad C/A, Chattogram',            '4100', 25680.00, 'COD', '8271639405', 1),
    (4, 'TRK628401937sadi',   6, 'Sadia Islam',  'sadia.islam@example.com', '+8801744444444', 'Zone 6, Uttara Sector 7, Dhaka',        '1230', 30000.00, 'COD', '5910372846', 0),
    (5, 'TRK394857102arif',   3, 'Arif Rahman',  'arif.rahman@example.com', '+8801711111111', 'Flat 3B, Shahjadpur, Gulshan, Dhaka',   '1212',  8400.00, 'COD', '1748029365', 4);

-- ------------------------------------------------------------
-- order_item : order lines. price is the unit price captured at
-- purchase time, so later catalogue edits never rewrite history.
-- ------------------------------------------------------------
INSERT INTO order_item (id, order_id, perfume_id, perfume_qty, price) VALUES
    -- Order 1 (delivered)  : 13800.00 + (2 x 4200.00) = 22200.00
    (1, 1,  1, 1, 13800.00),
    (2, 1, 22, 2,  4200.00),
    -- Order 2 (shipped)    : 19200.00                 = 19200.00
    (3, 2, 11, 1, 19200.00),
    -- Order 3 (completed)  : 18000.00 + (2 x 3840.00) = 25680.00
    (4, 3,  8, 1, 18000.00),
    (5, 3, 23, 2,  3840.00),
    -- Order 4 (processing) : 30000.00                 = 30000.00
    (6, 4, 14, 1, 30000.00),
    -- Order 5 (cancelled)  :  8400.00                 =  8400.00
    (7, 5, 19, 1,  8400.00);

SET FOREIGN_KEY_CHECKS = 1;

-- Re-sync AUTO_INCREMENT so new rows continue after the seeded ids.
ALTER TABLE customer   AUTO_INCREMENT = 7;
ALTER TABLE perfumes   AUTO_INCREMENT = 40;
ALTER TABLE cart       AUTO_INCREMENT = 7;
ALTER TABLE orders     AUTO_INCREMENT = 6;
ALTER TABLE order_item AUTO_INCREMENT = 8;
