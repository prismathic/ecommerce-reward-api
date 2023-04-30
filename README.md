# Ecommerce Rewards API (Bumpa Assessment).

## Introduction

Hi there 👋 <br>
Welcome to my task submission for the Bumpa test assessment. This project is a simple monolithic web application built with Laravel (v9)

## Task Description

The idea behind this project is to build a simple API for a rewards feature being integrated into an Ecommerce application where
users can unlock achievements based on completing a number of purchases and also unlock badges based on completing a number of achievements.

## Assumptions made

In building this application, the following assumptions were made:

-   The base currency of the system is NGN
-   The business making use of the system owns an existing Flutterwave account through which cashback would be transferred to users who unlock badges
-   Every user provides their bank account details during registration
-   An external service handles the webhook received from the payment provider for a payment

## Prerequisites

Running this application locally requires the following:

-   Composer (version 2 and above)
-   PHP 8.0+

## Setup Instructions

-   Clone the repository:

```
git clone https://github.com/prismathic/ecommerce-reward-api.git
```

-   Install Composer dependencies

```
composer install
```

-   Copy the sample environment variables and generate an application key

```
cp .env.example .env && php artisan key:generate
```

-   Set the values for your MySQL database connection in the env

```
DB_HOST=
DB_PORT=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

-   Set a value for the Flutterwave API key (this would be needed for initiating payments for cashbacks)

```
FLUTTERWAVE_SECRET_KEY=
```

-   Run the migration command and seed the default data (users, badges, achievements)

```
php artisan migrate --seed
```

-   Now you're ready to run the application 🎉

```
php artisan serve
```

-   Due to the application being a queue-based system, you might need to also run the queue worker alongside

```
php artisan queue:work
```

-   To run tests:

```
php artisan test
```

## Default Credentials/Endpoints

In order to use the application to initiate orders, it would require the user to be authenticated.<br>

While migrating (and seeding), a default user is created with the following credentials:

-   Email: `test@example.com`
-   Password: `password`

The Postman documentation (https://documenter.getpostman.com/view/13400573/2s93eSaG4M) outlines all existing endpoints on the application:

-   Authentication (Login) - `POST /api/auth/login` (With `email` and `password` in the request payload)
-   Authentication (Logout) - `POST /api/auth/logout`
-   Create Order - `POST /api/orders` (Requires authentication)
-   View user's achievements - `GET /api/users/:userId/achievements` (Does not require authentication)

## Links

-   [Postman Documentation](https://documenter.getpostman.com/view/13400573/2s93eSaG4M)
