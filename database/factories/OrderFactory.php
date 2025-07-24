<?php







namespace Database\Factories;

use App\Models\User;
use App\Models\Discount;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        $paymentStatus = $this->faker->randomElement(['unpaid', 'pending', 'paid', 'refunded', 'failed']);

        $fulfillmentStatus = $paymentStatus === 'paid'
            ? $this->faker->randomElement(['unfulfilled', 'fulfilled', 'returned', 'cancelled'])
            : 'unfulfilled';

        $deliveryStatus = $fulfillmentStatus === 'fulfilled'
            ? $this->faker->randomElement(['in_transit', 'delivered', 'failed'])
            : null;

        $hasDiscount = $this->faker->boolean(30);
        $discountId = $hasDiscount ? Discount::inRandomOrder()->first()?->id : null;
        $discountAmount = $hasDiscount ? $this->faker->randomFloat(2, 5, 100) : 0;

        $shippingAmount = $this->faker->randomFloat(2, 0, 50);
        $totalAmount = $this->faker->randomFloat(2, 50, 1000);

        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'order_date' => $this->faker->dateTimeBetween('-1 year', 'now'),

            'discount_id' => $discountId,
            'discount_amount' => $discountAmount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,

            'payment_status' => $paymentStatus,
            'fulfillment_status' => $fulfillmentStatus,
            'delivery_status' => $deliveryStatus,

            // New: Shipping address snapshot fields
            'shipping_full_name' => $this->faker->name,
            'shipping_phone' => $this->faker->phoneNumber,
            'shipping_dial_code' => '+234',
            'shipping_address_line_1' => $this->faker->streetAddress,
            'shipping_address_line_2' => $this->faker->optional()->secondaryAddress,
            'shipping_city' => $this->faker->city,
            'shipping_state' => $this->faker->state,
            'shipping_country' => $this->faker->country,
        ];
    }
}