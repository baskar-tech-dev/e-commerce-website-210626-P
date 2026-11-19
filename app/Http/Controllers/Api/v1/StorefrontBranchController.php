<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StorefrontBranchController extends Controller
{
    /**
     * Default Maya Sree Fashion official branches if not configured in settings.
     */
    public static function getDefaultBranches(): array
    {
        return [
            [
                'id' => 'tiruppur-boutique',
                'name' => 'Maya Sree Fashion - Flagship Boutique & Studio',
                'branch_tag' => 'Flagship Boutique',
                'address' => 'No. 4, Seenivasa Nagar 2nd Street, Solipalayam, Tiruppur – 641652',
                'city' => 'Tiruppur',
                'state' => 'Tamil Nadu',
                'postal_code' => '641652',
                'country' => 'India',
                'phone' => '+91 99442 85102',
                'email' => 'sales@mayasreefashion.com',
                'working_hours' => 'Mon – Sat: 9:30 AM – 8:00 PM | Sun: 10:00 AM – 2:00 PM',
                'map_embed_url' => 'https://maps.google.com/maps?q=Solipalayam+Tiruppur+Tamil+Nadu&t=&z=15&ie=UTF8&iwloc=&output=embed',
                'map_url' => 'https://maps.google.com/?q=Solipalayam+Tiruppur+Tamil+Nadu',
                'is_primary' => true,
            ],
            [
                'id' => 'tiruppur-factory',
                'name' => 'Maya Sree Fashion - Design & Weaving Unit',
                'branch_tag' => 'Manufacturing Unit',
                'address' => 'SF No. 128/2, Solipalayam Industrial Area, Tiruppur – 641652',
                'city' => 'Tiruppur',
                'state' => 'Tamil Nadu',
                'postal_code' => '641652',
                'country' => 'India',
                'phone' => '+91 99442 85103',
                'email' => 'production@mayasreefashion.com',
                'working_hours' => 'Mon – Sat: 8:30 AM – 7:00 PM | Sun: Closed',
                'map_embed_url' => 'https://maps.google.com/maps?q=Solipalayam+Industrial+Area+Tiruppur&t=&z=15&ie=UTF8&iwloc=&output=embed',
                'map_url' => 'https://maps.google.com/?q=Solipalayam+Industrial+Area+Tiruppur',
                'is_primary' => false,
            ],
            [
                'id' => 'coimbatore-wholesale',
                'name' => 'Maya Sree Fashion - Wholesale Experience Center',
                'branch_tag' => 'Wholesale & B2B Hub',
                'address' => 'Avinashi Road, Near Hope College, Peelamedu, Coimbatore – 641004',
                'city' => 'Coimbatore',
                'state' => 'Tamil Nadu',
                'postal_code' => '641004',
                'country' => 'India',
                'phone' => '+91 99442 85104',
                'email' => 'wholesale@mayasreefashion.com',
                'working_hours' => 'Mon – Sat: 10:00 AM – 8:00 PM | Sun: 11:00 AM – 6:00 PM',
                'map_embed_url' => 'https://maps.google.com/maps?q=Avinashi+Road+Peelamedu+Coimbatore&t=&z=15&ie=UTF8&iwloc=&output=embed',
                'map_url' => 'https://maps.google.com/?q=Avinashi+Road+Peelamedu+Coimbatore',
                'is_primary' => false,
            ],
        ];
    }

    /**
     * Get multi-branch store list for Contact Us page.
     */
    public function index(): JsonResponse
    {
        $branches = Setting::get('branches', 'brand_branches', null);

        if (empty($branches) || !is_array($branches)) {
            $branches = self::getDefaultBranches();
        }

        return response()->json([
            'success' => true,
            'data' => $branches,
        ]);
    }

    /**
     * Update Maya Sree Fashion multi-branch list.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branches' => 'required|array|min:1',
            'branches.*.name' => 'required|string|max:150',
            'branches.*.branch_tag' => 'nullable|string|max:80',
            'branches.*.address' => 'required|string|max:300',
            'branches.*.city' => 'nullable|string|max:100',
            'branches.*.state' => 'nullable|string|max:100',
            'branches.*.postal_code' => 'nullable|string|max:20',
            'branches.*.phone' => 'nullable|string|max:50',
            'branches.*.email' => 'nullable|string|max:100',
            'branches.*.working_hours' => 'nullable|string|max:150',
            'branches.*.map_embed_url' => 'nullable|string|max:1000',
            'branches.*.map_url' => 'nullable|string|max:1000',
            'branches.*.is_primary' => 'nullable|boolean',
        ]);

        $sanitized = [];
        foreach ($validated['branches'] as $idx => $b) {
            $name = trim($b['name']);
            $address = trim($b['address']);
            $city = !empty($b['city']) ? trim($b['city']) : '';
            $state = !empty($b['state']) ? trim($b['state']) : 'Tamil Nadu';
            $pincode = !empty($b['postal_code']) ? trim($b['postal_code']) : '';
            
            $destinationQuery = trim("{$name}, {$address}, {$city} {$pincode}");

            // Extract src if user pasted a full <iframe src="..." /> tag
            $rawEmbed = !empty($b['map_embed_url']) ? trim($b['map_embed_url']) : '';
            if (preg_match('/src=["\']([^"\']+)["\']/i', $rawEmbed, $matches)) {
                $rawEmbed = $matches[1];
            }
            $embedUrl = !empty($rawEmbed) ? $rawEmbed : ('https://maps.google.com/maps?q=' . urlencode($destinationQuery) . '&t=&z=15&ie=UTF8&iwloc=&output=embed');

            // Extract coordinates, place name, and CID from Google Maps embed pb parameter if present
            $lat = null;
            $lng = null;
            $cid = null;
            if (preg_match('/!2d(-?[0-9.]+)!3d(-?[0-9.]+)/', $embedUrl, $mCoords)) {
                $lng = $mCoords[1];
                $lat = $mCoords[2];
            }
            if (preg_match('/!1s0x[a-f0-9]+(?:%3A|:)(0x[a-f0-9]+)/i', $embedUrl, $mCid)) {
                $hex = $mCid[1];
                $cid = function_exists('gmp_hexdec') ? gmp_strval(gmp_hexdec($hex)) : sprintf('%u', hexdec($hex));
            }

            // Ensure map_url is not an embed iframe link, pointing to the exact pinned business listing
            $rawMapUrl = !empty($b['map_url']) ? trim($b['map_url']) : '';
            if (!empty($rawMapUrl) && !str_contains($rawMapUrl, '/embed')) {
                $mapUrl = $rawMapUrl;
            } elseif (!empty($cid)) {
                $mapUrl = 'https://maps.google.com/?cid=' . $cid;
            } elseif (!empty($lat) && !empty($lng)) {
                $mapUrl = "https://www.google.com/maps/search/?api=1&query={$lat},{$lng}";
            } else {
                $mapUrl = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($destinationQuery);
            }

            // Ensure directions_url navigates directly to the exact pin coordinates
            if (!empty($lat) && !empty($lng)) {
                $directionsUrl = "https://www.google.com/maps/dir/?api=1&destination={$lat},{$lng}";
            } elseif (!empty($cid)) {
                $directionsUrl = 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($name) . '&destination_place_id=' . $cid;
            } else {
                $directionsUrl = 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($destinationQuery);
            }

            $sanitized[] = [
                'id' => !empty($b['id']) ? $b['id'] : ('branch-' . ($idx + 1) . '-' . Str::slug($name)),
                'name' => $name,
                'branch_tag' => !empty($b['branch_tag']) ? trim($b['branch_tag']) : 'Store Branch',
                'address' => $address,
                'city' => $city,
                'state' => $state,
                'postal_code' => $pincode,
                'country' => 'India',
                'phone' => !empty($b['phone']) ? trim($b['phone']) : '',
                'email' => !empty($b['email']) ? trim($b['email']) : '',
                'working_hours' => !empty($b['working_hours']) ? trim($b['working_hours']) : 'Mon – Sat: 9:30 AM – 7:30 PM',
                'map_embed_url' => $embedUrl,
                'map_url' => $mapUrl,
                'directions_url' => $directionsUrl,
                'is_primary' => !empty($b['is_primary']),
            ];
        }

        Setting::set('branches', $sanitized, 'brand_branches', 'json');

        return response()->json([
            'success' => true,
            'message' => 'Maya Sree Fashion branch addresses updated successfully',
            'data' => $sanitized,
        ]);
    }
}
