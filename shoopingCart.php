<?php

// 1. Create a Checkout class with pricing rules
// 2. Create a function to allow scanning
// 3. Create a function to give back total

$pricingRules = [
    "products" => [
        "FR1" => [
            "id" => "FR1",
            "name" => "Fruit tea",
            "price" => 3.11
        ],
        "SR1" => [
            "id" => "SR1",
            "name" => "Strawberries",
            "price" => 5
        ],
        "CF1" => [
            "id" => "CF1",
            "name" => "Coffee",
            "price" => 11.23
        ],
    ],
    "offers" => [
        [
            "type" => "quantity_discount",
            "min_quantity" => 3,
            "discount" => .5,
            "product_code" => "SR1",
        ],
        [
            "type" => "bogof",
            "min_quantity" => 2,
            "discount" => 3.11,
            "product_code" => "FR1",
        ],
    ],
];

class Checkout 
{
    function __construct( $pricingRules )
    {
        $this->products = $pricingRules["products"];
        $this->offers = $pricingRules["offers"];
        $this->basket = [];
    }

    public function scan( $productCode )
    {
        $this->basket[] = $productCode;
    }

    public function total()
    {
        // 1. Loop through the basket
        // 2. Get each price
        // 3. Apply any offers
        $total = 0;

        foreach ( $this->basket as $itemCode ) {
            // FR1, SR1, CF1
            $price = $this->products[ $itemCode ]["price"];
            $total = $total + $price;
        }

        $total = $total - $this->getDiscount();

        return $total;
    }

    private function getDiscount() {
        $discount = 0;

        foreach ($this->offers as $offer) {
            switch ($offer["type"]) {
                case 'bogof':
                    $discount = $discount + $this->getBogofDiscount( $offer );
                    break;

                case 'quantity_discount':
                    $discount = $discount + $this->getQuantityDiscount( $offer );
                    break;
                
                default:
                    # code...
                    break;
            }
        }


        return $discount;
    }

    private function getBasketCount() {
        return array_count_values($this->basket);
    }

    private function getBasketCountForProductCode( $productCode ) {
        $counts = $this->getBasketCount();
        return $counts[ $productCode ] ?? 0;
    }

    private function getBogofDiscount( $offer )
    {
        $discount = 0;
        $count = $this->getBasketCountForProductCode( $offer["product_code"] );

        if ( $count < $offer["min_quantity"] ) return $discount;
        else if ( $count % 2  == 0 ) {
            $discount = ($count / 2) * $offer["discount"];
        } else {
            $discount = ( ($count - 1) / 2 ) * $offer["discount"];
        }
        return $discount;
    }

    private function getQuantityDiscount( $offer )
    {
        $discount = 0;
        $count = $this->getBasketCountForProductCode( $offer["product_code"] );

        if ( $count < $offer["min_quantity"] ) return $discount;
        else {
            $discount = $count * $offer["discount"];
        }

        return $discount;
    }
}

// TEST DATA

$baskets = [
    [
        "items" => ["FR1", "SR1", "FR1", "FR1", "CF1"],
        "expected_total" => 22.45,
    ],
    [
        "items" => ["FR1", "FR1"],
        "expected_total" => 3.11,
    ],
    [
        "items" => ["SR1", "SR1", "FR1", "SR1"],
        "expected_total" => 16.61,
    ],
    [
        "items" => ["CF1", "CF1"],
        "expected_total" => 11.23,
    ],
];

foreach ($baskets as $key => $basket) {
    $co = new Checkout( $pricingRules );

    foreach ($basket["items"] as $item) {
        $co->scan( $item );
    }

    echo "\n\n";
    echo "basket id = " . $key;
    echo "\ntotal = " . $co->total();
    echo "\nexpectedTotal = " . $basket["expected_total"];
    echo "\nVALID = " . ($co->total() === $basket["expected_total"] ? "VALID" : "INVALID");
    echo "\n\n";
}
