CREATE DATABASE IF NOT EXISTS seat_booking;
USE seat_booking;

-- Create bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    seat_number INT NOT NULL,
    booking_date DATETIME NOT NULL,
    UNIQUE KEY unique_seat (seat_number)
);