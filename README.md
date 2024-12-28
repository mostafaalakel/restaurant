# Restaurant Management Api

## Description:
This project is a comprehensive Restaurant Management System developed using Laravel, designed to streamline various operations of a restaurant. The system provides a seamless experience for both customers and restaurant staff, allowing users to browse food items, add them to a cart, make secure payments via PayPal, and even reserve tables. The system also supports user-generated reviews to improve services, while the administrative roles ensure efficient restaurant management through an easy-to-use dashboard. The API supports multiple user roles, enabling different levels of access to restaurant data and operations.

### Key Features:
- **Multi-role Authentication**: Different access levels for Admin, and User using JWT Authentication package.
- **Google Authentication**: Implemented login via Google accounts using the Laravel Socialite package to enhance user convenience and security.
- **Multi-language Support**: Added multilingual support for Arabic and English using the Laravel Translatable package, storing translatable fields as JSON for optimized queries.
- **Cart Management**: Users can add, view, update, and remove items from the cart before proceeding to checkout.
- **Discount Management**: Added functionality for food-specific discount codes and general discounts applicable to all users.
- **Order Processing**: Handled order creation, including calculating total prices based on cart items and managing order statuses (pending, paid).
- **PayPal Payment Integration**: Facilitates secure payments, order processing, and real-time payment status updates.
- **Reservation System**: Implemented a reservation system to allow users to book tables.
- **Review System**: Users can submit reviews for food items, which enhances customer feedback and helps improve services.
- **Database Relationships**: Utilized Eloquent ORM to manage complex relationships between tables (e.g., orders, order items, cart items, general discounts, code discounts), allowing for efficient data retrieval and manipulation.
- **Automated Sales Reporting**: Developed a job and queue system to automatically generate and email periodic sales reports to the admin.
- **Scheduled Tasks**: Leveraged Laravel Scheduler for automating recurring tasks like report generation.

