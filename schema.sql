CREATE TABLE users (
  id INT PRIMARY KEY,
  name VARCHAR,
  email VARCHAR,
  password VARCHAR
);

CREATE TABLE products (
  id INT PRIMARY KEY,
  name VARCHAR,
  price DECIMAL
);

CREATE TABLE orders (
  id INT PRIMARY KEY,
  userId INT,
  total DECIMAL
);

CREATE TABLE order_items (
  id INT PRIMARY KEY,
  orderId INT,
  productId INT,
  quantity INT
);