-- Categories for jewelry shop
INSERT INTO categories (name, description) VALUES
('Rings', 'Beautiful rings collection including engagement and wedding rings'),
('Necklaces', 'Elegant necklaces and pendants'),
('Earrings', 'Stunning earrings collection'),
('Bracelets', 'Stylish bracelets and bangles'),
('Wedding Jewelry', 'Special collection for wedding and engagement'),
('Gold Bars', 'Investment grade gold bars'),
('Diamonds', 'Premium diamond jewelry'),
('Pearls', 'Elegant pearl jewelry collection');

-- Sample products
INSERT INTO products (name, category_id, price, sale_price, description, material, weight, stone_type, stone_size, stock_status, created_at) VALUES
('Diamond Engagement Ring', 1, 2500.00, 2300.00, '18K Gold Diamond Engagement Ring', '18K Gold', 0.5, 'Diamond', '1 carat', 'in_stock', NOW()),
('Pearl Necklace', 2, 1200.00, 1000.00, 'Elegant Pearl Necklace with Gold Chain', '18K Gold', 1.0, 'Pearl', '8mm', 'in_stock', NOW()),
('Gold Hoop Earrings', 3, 800.00, NULL, 'Classic Gold Hoop Earrings', '18K Gold', 0.3, NULL, NULL, 'in_stock', NOW()),
('Diamond Tennis Bracelet', 4, 3500.00, 3200.00, 'Diamond Tennis Bracelet in White Gold', '18K White Gold', 0.8, 'Diamond', '0.1 carat each', 'in_stock', NOW()),
('Wedding Ring Set', 5, 4000.00, 3800.00, 'Matching Wedding Ring Set with Diamonds', '18K Gold', 0.6, 'Diamond', '0.5 carat total', 'in_stock', NOW()),
('24K Gold Bar 100g', 6, 6500.00, NULL, 'Investment Grade 24K Gold Bar - 100 grams', '24K Gold', 100.0, NULL, NULL, 'in_stock', NOW());

-- Sample product images
INSERT INTO product_images (product_id, image_url, is_primary) VALUES
(1, '/Assignment/assets/images/products/ring1.jpg', 1),
(1, '/Assignment/assets/images/products/ring1-2.jpg', 0),
(2, '/Assignment/assets/images/products/necklace1.jpg', 1),
(3, '/Assignment/assets/images/products/earring1.jpg', 1),
(4, '/Assignment/assets/images/products/bracelet1.jpg', 1),
(5, '/Assignment/assets/images/products/wedding-set1.jpg', 1),
(6, '/Assignment/assets/images/products/gold-bar1.jpg', 1);

-- Admin account (password: 180311202)
INSERT INTO admins (email, password, create_at) VALUES
('admin@gmail.com', '180311202', NOW());
