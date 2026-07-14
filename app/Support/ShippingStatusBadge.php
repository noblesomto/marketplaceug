<?php

namespace App\Support;

class ShippingStatusBadge
{
    /**
     * Resolve the single order-level status badge shown to both seller and buyer.
     * Priority: buyer_status (once delivered) > shipping_status (once the shipper
     * has taken over) > seller_status (before the shipper has touched it).
     */
    public static function resolve(string $sellerStatus, string $shipStatus, string $buyerStatus, string $shipCompany): array
    {
        if ($buyerStatus === 'delivered') {
            return ['stage' => 'delivered', 'label' => 'Delivered', 'icon' => 'bi-check-circle-fill', 'class' => 'bg-green-100 text-green-700 border-green-200'];
        }

        if ($shipStatus !== 'pending') {
            return match ($shipStatus) {
                'delivered' => ['stage' => 'ship_delivered', 'label' => 'Pending Confirmation', 'icon' => 'bi-hourglass-split', 'class' => 'bg-orange-100 text-orange-700 border-orange-200'],
                'shipped'   => ['stage' => 'ship_shipped', 'label' => 'Shipped', 'icon' => 'bi-truck', 'class' => 'bg-blue-100 text-blue-700 border-blue-200'],
                'pickup'    => ['stage' => 'ship_pickup', 'label' => 'Ready for Pickup', 'icon' => 'bi-shop', 'class' => 'bg-purple-100 text-purple-700 border-purple-200'],
                'canceled'  => ['stage' => 'canceled', 'label' => 'Canceled', 'icon' => 'bi-x-circle-fill', 'class' => 'bg-red-100 text-red-700 border-red-200'],
                default     => ['stage' => 'pending', 'label' => 'Pending', 'icon' => 'bi-clock', 'class' => 'bg-amber-100 text-amber-700 border-amber-200'],
            };
        }

        if ($sellerStatus === 'shipped') {
            return ['stage' => 'seller_shipped', 'label' => "Dropped off at {$shipCompany}", 'icon' => 'bi-box-seam-fill', 'class' => 'bg-blue-100 text-blue-700 border-blue-200'];
        }

        if ($sellerStatus === 'canceled') {
            return ['stage' => 'canceled', 'label' => 'Canceled', 'icon' => 'bi-x-circle-fill', 'class' => 'bg-red-100 text-red-700 border-red-200'];
        }

        return ['stage' => 'pending', 'label' => 'Pending', 'icon' => 'bi-clock', 'class' => 'bg-amber-100 text-amber-700 border-amber-200'];
    }

    /**
     * Resolve the courier's own badge, shown next to the shipping company
     * (e.g. in the "Selected Shipping" card). Reflects shipping_status only —
     * not seller_status or buyer_status.
     */
    public static function resolveShippingOnly(string $shipStatus): array
    {
        return match ($shipStatus) {
            'delivered' => ['label' => 'Delivered', 'icon' => 'bi-check-circle-fill', 'class' => 'bg-green-100 text-green-700 border-green-200'],
            'shipped'   => ['label' => 'Shipped', 'icon' => 'bi-truck', 'class' => 'bg-blue-100 text-blue-700 border-blue-200'],
            'pickup'    => ['label' => 'Ready for Pickup', 'icon' => 'bi-shop', 'class' => 'bg-purple-100 text-purple-700 border-purple-200'],
            'canceled'  => ['label' => 'Canceled', 'icon' => 'bi-x-circle-fill', 'class' => 'bg-red-100 text-red-700 border-red-200'],
            default     => ['label' => 'Pending', 'icon' => 'bi-clock', 'class' => 'bg-amber-100 text-amber-700 border-amber-200'],
        };
    }
}
