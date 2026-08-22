<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Courier;

class CourierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $couriers = [
            [
                'name' => 'Delhivery',
                'code' => 'delhivery',
                'tracking_page_link' => 'https://www.delhivery.com/track/package/{tracking_number}',
                'contact_person' => 'Delhivery Account Executive',
                'contact_number' => '+91 80698 56100',
                'contact_email' => 'customer.support@delhivery.com',
                'is_active' => true,
                'sort_order' => 1,
                'notes' => 'Primary express delivery partner for South & Pan-India deliveries.',
            ],
            [
                'name' => 'Blue Dart',
                'code' => 'bluedart',
                'tracking_page_link' => 'https://www.bluedart.com/tracking?track={tracking_number}',
                'contact_person' => 'Blue Dart Hub Executive',
                'contact_number' => '+91 1860 233 1234',
                'contact_email' => 'track@bluedart.com',
                'is_active' => true,
                'sort_order' => 2,
                'notes' => 'Air express courier partner for premium & fast deliveries.',
            ],
            [
                'name' => 'DTDC Express',
                'code' => 'dtdc',
                'tracking_page_link' => 'https://www.dtdc.in/tracking/shipment-tracking.asp?ref={tracking_number}',
                'contact_person' => 'DTDC Area Manager',
                'contact_number' => '+91 73057 73057',
                'contact_email' => 'customersupport@dtdc.com',
                'is_active' => true,
                'sort_order' => 3,
                'notes' => 'Reliable regional and interstate delivery service.',
            ],
            [
                'name' => 'The Professional Couriers',
                'code' => 'professional',
                'tracking_page_link' => 'https://www.tpcindia.com/tracking.aspx?con={tracking_number}',
                'contact_person' => 'TPC Dispatch Incharge',
                'contact_number' => '+91 44 2855 5555',
                'contact_email' => 'support@tpcindia.com',
                'is_active' => true,
                'sort_order' => 4,
                'notes' => 'Specialized coverage across Tamil Nadu and South India.',
            ],
            [
                'name' => 'ST Courier',
                'code' => 'st_courier',
                'tracking_page_link' => 'https://stcourier.com/track/details/{tracking_number}',
                'contact_person' => 'ST Courier Tirupur Hub',
                'contact_number' => '+91 98424 00000',
                'contact_email' => 'info@stcourier.com',
                'is_active' => true,
                'sort_order' => 5,
                'notes' => 'Fast overnight regional delivery across South India.',
            ],
            [
                'name' => 'India Post (Speed Post)',
                'code' => 'india_post',
                'tracking_page_link' => 'https://www.indiapost.gov.in/_layouts/15/dpt.cept.tracking/trackconsignment.aspx?cons={tracking_number}',
                'contact_person' => 'Head Post Office Executive',
                'contact_number' => '+91 1800 266 6868',
                'contact_email' => 'care@indiapost.gov.in',
                'is_active' => true,
                'sort_order' => 6,
                'notes' => 'All-India postal pin-code coverage including rural zones.',
            ],
            [
                'name' => 'Shadowfax',
                'code' => 'shadowfax',
                'tracking_page_link' => 'https://tracker.shadowfax.in/#/track?orderId={tracking_number}',
                'contact_person' => 'Shadowfax Hub Lead',
                'contact_number' => '+91 80 6817 2500',
                'contact_email' => 'help@shadowfax.in',
                'is_active' => true,
                'sort_order' => 7,
                'notes' => 'Hyperlocal and e-commerce express logistics.',
            ],
        ];

        foreach ($couriers as $courier) {
            Courier::updateOrCreate(
                ['name' => $courier['name']],
                $courier
            );
        }
    }
}
