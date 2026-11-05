# CommonCents

CommonCents is a student marketplace built with PHP, HTML, CSS, JavaScript and MySQL.
The marketplace frontend styles and images from the separate `Frontend.zip` have been integrated into the existing PHP project and connected to its MySQL data models.

## Setup with WAMP

1. Copy the `CommonCents` folder into `C:\wamp64\www\`.
2. Start Apache and MySQL in WAMP.
3. In phpMyAdmin, create/select the database named `commoncents` and import `data/commoncents.sql` if the tables are not already present.
4. Check `config/database.php` and update the MySQL username/password if your local WAMP credentials differ. The configured database name is `commoncents`.
5. Open `http://localhost/CommonCents/`.

## Integrated pages

- `frontend/index.php` — public marketplace landing page and product search
- `frontend/home.php` — signed-in marketplace homepage
- `frontend/listing.php` — listing details and add-to-cart action
- `frontend/cart.php` — cart quantity updates, item removal and subtotal
- `frontend/checkout.php` — checkout, order creation and pending payment record
- `frontend/orders.php` — signed-in user's order history
- `frontend/order-details.php` — order items, delivery details and payment status

Frontend styles and images copied from the supplied frontend ZIP are stored under `assets/frontend/`.

## Payment note

Checkout records a payment with `Pending` status. The current project does not integrate a real payment gateway, so choosing Card, EFT or PayFast does not itself charge a payment method or confirm a payment.
