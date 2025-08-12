-- Create Database for Delicious Eats Ordering System
-- Database: delicious_eats

-- Create the database
CREATE DATABASE IF NOT EXISTS delicious_eats;
USE delicious_eats;

-- Create Users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    zip_code VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Create Menu table
CREATE TABLE IF NOT EXISTS menu (
    menu_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category ENUM('pizza', 'burgers', 'pasta', 'salads', 'desserts', 'beverages') NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255),
    is_available BOOLEAN DEFAULT TRUE,
    ingredients TEXT,
    nutritional_info TEXT,
    preparation_time INT DEFAULT 15, -- in minutes
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create Orders table (additional table for complete ordering system)
CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled') DEFAULT 'pending',
    delivery_address TEXT,
    phone VARCHAR(15),
    special_instructions TEXT,
    payment_method ENUM('cash', 'credit_card', 'debit_card', 'paypal', 'online') DEFAULT 'cash',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    estimated_delivery TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Create Events table
CREATE TABLE IF NOT EXISTS events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(100) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    location VARCHAR(200) NOT NULL,
    capacity INT DEFAULT 50,
    price DECIMAL(10, 2) DEFAULT 0.00,
    event_type ENUM('workshop', 'tasting', 'party', 'cooking_class', 'special_dinner', 'other') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    image_url VARCHAR(255),
    contact_email VARCHAR(100),
    contact_phone VARCHAR(15),
    requirements TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create Order Items table (junction table for orders and menu items)
CREATE TABLE IF NOT EXISTS order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    special_requests TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (menu_id) REFERENCES menu(menu_id) ON DELETE CASCADE
);

-- Insert sample menu data based on your products.php
INSERT INTO menu (name, description, category, price, image_url, ingredients, preparation_time) VALUES
('Margherita Pizza', 'Classic pizza with tomato sauce, fresh mozzarella, basil leaves, and olive oil.', 'pizza', 14.99, '../assets/images/products/placeholder.jpg', 'Pizza dough, Tomato sauce, Fresh mozzarella, Basil leaves, Olive oil', 20),
('Grilled Salmon', 'Perfectly grilled salmon fillet with lemon herb butter and seasonal vegetables.', 'salads', 22.99, '../assets/images/products/placeholder.jpg', 'Fresh salmon fillet, Lemon, Herbs, Butter, Seasonal vegetables', 25),
('Beef Burger', 'Juicy beef patty with lettuce, tomato, cheese, and our special sauce on a brioche bun.', 'burgers', 12.99, '../assets/images/products/placeholder.jpg', 'Beef patty, Lettuce, Tomato, Cheese, Special sauce, Brioche bun', 15),
('Caesar Salad', 'Fresh romaine lettuce with parmesan cheese, croutons, and our house Caesar dressing.', 'salads', 9.99, '../assets/images/products/placeholder.jpg', 'Romaine lettuce, Parmesan cheese, Croutons, Caesar dressing', 10),
('Pasta Carbonara', 'Al dente spaghetti with crispy bacon, creamy egg sauce, and freshly ground black pepper.', 'pasta', 16.99, '../assets/images/products/placeholder.jpg', 'Spaghetti, Bacon, Eggs, Cream, Black pepper, Parmesan cheese', 18),
('Chocolate Cake', 'Rich, moist chocolate cake with ganache frosting and fresh berries on top.', 'desserts', 8.99, '../assets/images/products/placeholder.jpg', 'Chocolate cake, Ganache frosting, Fresh berries', 5);

-- Insert sample beverages
INSERT INTO menu (name, description, category, price, image_url, ingredients, preparation_time) VALUES
('Fresh Orange Juice', 'Freshly squeezed orange juice served chilled.', 'beverages', 4.99, '../assets/images/products/placeholder.jpg', 'Fresh oranges', 3),
('Italian Coffee', 'Rich espresso coffee served with a touch of cream.', 'beverages', 3.99, '../assets/images/products/placeholder.jpg', 'Espresso beans, Cream', 5),
('Iced Tea', 'Refreshing iced tea with lemon slices.', 'beverages', 2.99, '../assets/images/products/placeholder.jpg', 'Black tea, Ice, Lemon', 2);

-- Insert sample users (for testing)
INSERT INTO users (first_name, last_name, email, password, phone, address, city, state, zip_code) VALUES
('John', 'Doe', 'john.doe@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-123-4567', '123 Main St', 'Anytown', 'CA', '12345'),
('Jane', 'Smith', 'jane.smith@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-987-6543', '456 Oak Ave', 'Somewhere', 'NY', '67890'),
('Mike', 'Johnson', 'mike.johnson@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-456-7890', '789 Pine Rd', 'Elsewhere', 'TX', '54321');

-- Insert sample events
INSERT INTO events (event_name, description, event_date, event_time, location, capacity, price, event_type, contact_email, contact_phone, requirements, image_url) VALUES
('Italian Cooking Workshop', 'Learn to make authentic Italian pasta and sauces from our head chef. Includes hands-on cooking, wine tasting, and a 3-course meal.', '2025-09-15', '18:00:00', 'Main Kitchen & Private Dining Room', 20, 75.00, 'cooking_class', 'events@deliciouseats.com', '555-EVENTS', 'Aprons provided. Comfortable shoes recommended.', '../assets/images/events/placeholder.jpg'),
('Wine & Cheese Tasting', 'An evening of fine wines paired with artisanal cheeses. Guided by our sommelier with detailed tasting notes.', '2025-09-22', '19:30:00', 'Wine Cellar', 15, 45.00, 'tasting', 'events@deliciouseats.com', '555-EVENTS', 'Must be 21+. Valid ID required.', '../assets/images/events/placeholder.jpg'),
('Birthday Party Package', 'Private party room with customizable menu, decorations, and dedicated staff. Perfect for special celebrations.', '2025-10-01', '17:00:00', 'Private Party Room', 40, 25.00, 'party', 'parties@deliciouseats.com', '555-PARTY', 'Advance booking required. Minimum 10 guests.', '../assets/images/events/placeholder.jpg'),
('Farm-to-Table Special Dinner', 'Exclusive 5-course dinner featuring locally sourced ingredients from partner farms. Limited seating for an intimate experience.', '2025-10-15', '18:30:00', 'Chef''s Table', 12, 120.00, 'special_dinner', 'chef@deliciouseats.com', '555-CHEF', 'Dietary restrictions accommodated with advance notice.', '../assets/images/events/placeholder.jpg'),
('Kids Cooking Class', 'Fun cooking class for children ages 8-14. Learn to make pizzas, cookies, and healthy snacks in a safe, supervised environment.', '2025-10-28', '14:00:00', 'Kids Activity Room', 12, 35.00, 'workshop', 'kids@deliciouseats.com', '555-KIDS', 'Ages 8-14. Parent/guardian must sign waiver.', '../assets/images/events/placeholder.jpg'),
('Holiday Catering Showcase', 'Sample our holiday catering menu and place advance orders for your holiday parties and corporate events.', '2025-11-20', '16:00:00', 'Main Dining Room', 50, 0.00, 'other', 'catering@deliciouseats.com', '555-CATER', 'Free event. RSVP required for accurate headcount.', '../assets/images/events/placeholder.jpg');

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_menu_category ON menu(category);
CREATE INDEX idx_menu_available ON menu(is_available);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_order_items_order ON order_items(order_id);
CREATE INDEX idx_order_items_menu ON order_items(menu_id);
CREATE INDEX idx_events_date ON events(event_date);
CREATE INDEX idx_events_type ON events(event_type);
CREATE INDEX idx_events_active ON events(is_active);

-- Display created tables
SHOW TABLES;

-- Display table structures
DESCRIBE users;
DESCRIBE menu;
DESCRIBE orders;
DESCRIBE order_items;
