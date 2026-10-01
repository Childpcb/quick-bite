-- Insert admin user (password: admin123)
INSERT INTO users (name, email, phone, password, role, status) VALUES
('Admin', 'admin@quickbite.com', '+255700000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active');

-- Insert sample customers (password: user123)
INSERT INTO users (name, email, phone, password, role, status) VALUES
('Alison Naftali', 'alisonnaftali2003@gmail.com', '+255754341171', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'active'),
('Consolata Peter', 'consolata.peter@example.com', '+255713456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'active'),
('Anicetha Naftali', 'anicetha.naftali@example.com', '+255712345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'active'),
('Alloyce Naftali', 'alloyce.naftali@example.com', '+255715566778', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'active'),
('Anivia Naftali', 'anivia.naftali@example.com', '+255716677889', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'active'),
('Frola Kazoni', 'frola.kazoni@example.com', '+255717788990', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'active');

-- Insert restaurants (with FULL details)
INSERT INTO restaurants (name, description, phone, email, address, city, region, delivery_fee, delivery_time, opening_hours, rating) VALUES
('Quick Grill', 'Specialized in burgers, sandwiches, and fast food. We serve the best grilled chicken, beef burgers, and crispy French fries. Our restaurant is known for its quick service and delicious food prepared fresh daily.', '+255700111222', 'info@quickgrill.com', '12 Samora Avenue', 'Shinyanga', 'Shinyanga', 3000, '25-35 min', '10:00 AM - 10:00 PM', 4.5),
('Pizza Palace', 'Authentic Italian pizza and pasta dishes. We use fresh ingredients and traditional recipes to bring you the best pizza experience in Shinyanga. Our menu includes Margherita, Chicken, Pepperoni, and more.', '+255700333444', 'info@pizzapalace.com', '45 Mwenge Road', 'Shinyanga', 'Shinyanga', 5000, '30-40 min', '9:00 AM - 11:00 PM', 4.8),
('Spice Corner', 'Tanzanian and Indian cuisine, spicy and flavorful. We specialize in chicken biryani, butter chicken, pilau rice, and other delicious East African dishes. Our spices are imported directly from India.', '+255700555666', 'info@spicecorner.com', '8 Tandale Street', 'Shinyanga', 'Shinyanga', 3000, '20-30 min', '10:00 AM - 10:00 PM', 4.7),
('Fresh Bites', 'Healthy salads, sandwiches, and fresh juices. We believe in eating fresh and healthy. Our menu includes grilled salmon, quinoa salad, fresh juices, and more. Perfect for those who want to eat clean.', '+255700777888', 'info@freshbites.com', '22 Uhuru Road', 'Shinyanga', 'Shinyanga', 2500, '15-25 min', '8:00 AM - 9:00 PM', 4.3);

-- Insert categories
INSERT INTO categories (name, icon) VALUES
('Pizza', '🍕'),
('Burgers', '🍔'),
('Chicken', '🍗'),
('Fast Food', '🍟'),
('African', '🍛'),
('Indian', '🍛'),
('Drinks', '🥤'),
('Desserts', '🍰'),
('Rice', '🍚'),
('Healthy', '🥗');

-- Insert food items
INSERT INTO food_items (name, description, ingredients, price, restaurant_id, category_id, image, is_popular, is_featured, rating) VALUES
('Chicken Burger', 'Grilled chicken breast with lettuce, tomato, and special sauce', 'Chicken, lettuce, tomato, sauce, bun', 12000, 1, 2, 'chicken-burger.jpg', 1, 1, 4.5),
('Beef Burger', 'Juicy beef patty with cheese, lettuce, and pickles', 'Beef, cheese, lettuce, pickles, bun', 15000, 1, 2, 'beef-burger.jpg', 1, 1, 4.7),
('French Fries', 'Crispy golden French fries with ketchup', 'Potatoes, oil, salt', 5000, 1, 4, 'french-fries.jpg', 1, 0, 4.3),
('Margherita Pizza', 'Classic pizza with tomato sauce, mozzarella, and basil', 'Dough, tomato sauce, mozzarella, basil', 18000, 2, 1, 'margherita.jpg', 1, 1, 4.8),
('Chicken Pizza', 'Pizza with grilled chicken, onions, and peppers', 'Dough, tomato sauce, chicken, onions, peppers', 22000, 2, 1, 'chicken-pizza.jpg', 1, 0, 4.6),
('Pepperoni Pizza', 'Pizza with pepperoni and cheese', 'Dough, tomato sauce, pepperoni, cheese', 20000, 2, 1, 'pepperoni-pizza.jpg', 0, 0, 4.5),
('Chicken Biryani', 'Spicy chicken biryani with basmati rice', 'Chicken, rice, spices, onions, tomatoes', 15000, 3, 5, 'chicken-biryani.jpg', 1, 1, 4.9),
('Butter Chicken', 'Creamy butter chicken with naan bread', 'Chicken, cream, butter, spices, naan', 16000, 3, 6, 'butter-chicken.jpg', 1, 0, 4.8),
('Pilau Rice', 'Tanzanian spiced rice with beef', 'Rice, beef, spices, onions', 12000, 3, 9, 'pilau-rice.jpg', 0, 0, 4.4),
('Grilled Salmon', 'Fresh salmon grilled with vegetables', 'Salmon, vegetables, lemon, herbs', 25000, 4, 10, 'grilled-salmon.jpg', 0, 1, 4.6),
('Quinoa Salad', 'Healthy quinoa salad with fresh vegetables', 'Quinoa, lettuce, tomatoes, cucumbers, dressing', 10000, 4, 10, 'quinoa-salad.jpg', 0, 1, 4.5),
('Fresh Juice', 'Freshly squeezed orange juice', 'Oranges, ice', 4000, 4, 7, 'fresh-juice.jpg', 1, 0, 4.7);

-- Insert promotions
INSERT INTO promotions (title, description, promo_code, discount_type, discount_value, start_date, end_date, status) VALUES
('First Order Discount', 'Get 20% off your first order', 'WELCOME20', 'percentage', 20, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 'active'),
('Free Delivery Weekend', 'Free delivery on all orders this weekend', 'FREEDEL', 'free_delivery', 0, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 'active'),
('Pizza Wednesday', 'Special 15% off on all pizzas', 'PIZZA15', 'percentage', 15, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 'active');

-- Insert settings
INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'Quick Bite'),
('site_email', 'alisonnaftali2003@gmail.com'),
('site_phone_1', '+255754341171'),
('site_phone_2', '619811171'),
('site_location', 'Shinyanga, Tanzania'),
('delivery_fee', '3000'),
('currency', 'TZS');