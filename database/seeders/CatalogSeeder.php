<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Realistic starter catalogue so the shop looks alive on day one. Prices are
 * indicative (pre-GST) and meant to be edited from the admin panel.
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(fn () => $this->seed());
    }

    private function seed(): void
    {
        $categories = collect([
            ['Bolts', 'bolts', 'Hex, allen, carriage, eye and foundation bolts in MS, SS and high-tensile grades.'],
            ['Nuts', 'nuts', 'Hex, nylock, flange, wing and dome nuts to match every bolt.'],
            ['Screws', 'screws', 'Self-tapping, wood, drywall, machine and roofing screws.'],
            ['Washers', 'washers', 'Plain, spring, fender and sealing washers.'],
            ['Anchors & Plugs', 'anchors', 'Wedge anchors, sleeve anchors and nylon wall plugs for concrete & brick.'],
            ['Threaded Rods', 'rods', 'Fully threaded studs and rods for hanging, framing and anchoring.'],
            ['Rivets', 'rivets', 'Blind (pop) rivets in aluminium and stainless steel.'],
            ['Hand Tools', 'tools', 'Spanners, allen keys, drill bits and fastening tools.'],
        ])->mapWithKeys(fn (array $row, int $index) => [$row[1] => Category::updateOrCreate(
            ['slug' => Str::slug($row[0])],
            ['name' => $row[0], 'icon' => $row[1], 'description' => $row[2], 'sort_order' => $index + 1],
        )]);

        $metric = fn (array $diameters, array $lengths) => collect($diameters)
            ->crossJoin($lengths)
            ->map(fn ($pair) => $pair[0].' × '.$pair[1].' mm')
            ->all();

        foreach ($this->products($metric) as $item) {
            $product = Product::updateOrCreate(
                ['slug' => Str::slug($item['name'])],
                [
                    'category_id' => $categories[$item['category']]->id,
                    'name' => $item['name'],
                    'brand' => $item['brand'] ?? 'BoltMart',
                    'material' => $item['material'],
                    'finish' => $item['finish'] ?? null,
                    'standard' => $item['standard'] ?? null,
                    'hsn_code' => $item['hsn'] ?? '7318',
                    'gst_rate' => 18,
                    'description' => $item['description'],
                    'specs' => $item['specs'] ?? [],
                    'is_active' => true,
                    'is_featured' => $item['featured'] ?? false,
                ],
            );

            $prefix = $item['sku'];
            foreach ($item['sizes'] as $index => $size) {
                $piecePrice = $item['base'] * (1 + $index * ($item['step'] ?? 0.22));
                $pack = $item['pack'];
                $price = max(9, round($piecePrice * $pack));
                $code = preg_replace('/[^A-Z0-9.\-]/', '', strtoupper(str_replace(['×', 'mm'], ['X', ''], $size)));

                $variant = $product->variants()->updateOrCreate(
                    ['sku' => $prefix.'-'.$code],
                    [
                        'size' => $size,
                        'pack_qty' => $pack,
                        'price' => $price,
                        'mrp' => round($price * 1.25),
                        'stock' => ($index % 7 === 5) ? 4 : 40 + (($index * 37) % 260),
                        'min_order_qty' => 1,
                    ],
                );

                $variant->priceTiers()->delete();
                $variant->priceTiers()->createMany([
                    ['min_qty' => 10, 'unit_price' => round($price * 0.95, 2)],
                    ['min_qty' => 50, 'unit_price' => round($price * 0.90, 2)],
                ]);
            }
        }
    }

    /**
     * @param  callable(array<int, string>, array<int, int>): array<int, string>  $metric
     * @return list<array<string, mixed>>
     */
    private function products(callable $metric): array
    {
        return [
            [
                'category' => 'bolts', 'name' => 'Hex Head Bolt SS304 (Full Thread)', 'sku' => 'HB304', 'material' => 'Stainless Steel 304',
                'finish' => 'Plain', 'standard' => 'DIN 933', 'featured' => true, 'base' => 6.5, 'pack' => 50, 'step' => 0.18,
                'sizes' => $metric(['M6', 'M8', 'M10', 'M12'], [25, 40, 60]),
                'description' => 'Corrosion-resistant A2 stainless hex bolts for outdoor fabrication, railings, food-grade and marine-adjacent work.',
                'specs' => ['Grade' => 'A2-70', 'Thread' => 'Metric coarse', 'Head' => 'Hexagon', 'Tensile strength' => '700 MPa'],
            ],
            [
                'category' => 'bolts', 'name' => 'High Tensile Hex Bolt 8.8 (Black)', 'sku' => 'HT88', 'material' => 'High Tensile Steel',
                'finish' => 'Black Oxide', 'standard' => 'IS 1364 / DIN 931', 'featured' => true, 'base' => 4.2, 'pack' => 50, 'step' => 0.2,
                'sizes' => $metric(['M10', 'M12', 'M16'], [50, 75, 100]),
                'description' => 'Grade 8.8 structural bolts for machinery, steel structures and heavy fabrication. Half thread for shear strength.',
                'specs' => ['Grade' => '8.8', 'Thread' => 'Half thread', 'Tensile strength' => '800 MPa', 'Hardness' => 'HRC 22–32'],
            ],
            [
                'category' => 'bolts', 'name' => 'Allen Cap Screw (Socket Head) 12.9', 'sku' => 'SHCS', 'material' => 'Alloy Steel',
                'finish' => 'Black Oxide', 'standard' => 'DIN 912', 'featured' => true, 'base' => 3.8, 'pack' => 100, 'step' => 0.2,
                'sizes' => $metric(['M5', 'M6', 'M8'], [16, 25, 40]),
                'description' => 'Unbrako-pattern socket head cap screws for dies, jigs, automation and machine assembly.',
                'specs' => ['Grade' => '12.9', 'Drive' => 'Hex socket', 'Tensile strength' => '1220 MPa'],
            ],
            [
                'category' => 'bolts', 'name' => 'Carriage Bolt with Nut (Zinc Plated)', 'sku' => 'CB', 'material' => 'Mild Steel',
                'finish' => 'Zinc Plated', 'standard' => 'DIN 603', 'base' => 3.1, 'pack' => 50,
                'sizes' => $metric(['M8', 'M10'], [50, 75, 100]),
                'description' => 'Mushroom-head, square-neck bolts for wooden structures, gates and furniture. Supplied with matching hex nuts.',
                'specs' => ['Head' => 'Round dome', 'Neck' => 'Square', 'Includes' => 'Hex nut'],
            ],
            [
                'category' => 'bolts', 'name' => 'Eye Bolt Forged (Lifting)', 'sku' => 'EYE', 'material' => 'Forged Carbon Steel',
                'finish' => 'Zinc Plated', 'standard' => 'DIN 580', 'base' => 45, 'pack' => 1, 'step' => 0.45,
                'sizes' => ['M8', 'M10', 'M12', 'M16', 'M20'],
                'description' => 'Drop-forged collar eye bolts for lifting and rigging. Check working load limit before use.',
                'specs' => ['WLL (M8)' => '140 kg', 'WLL (M12)' => '340 kg', 'WLL (M20)' => '1200 kg'],
            ],
            [
                'category' => 'bolts', 'name' => 'Foundation J-Bolt (Anchor Bolt)', 'sku' => 'JB', 'material' => 'Mild Steel',
                'finish' => 'Plain', 'standard' => 'IS 5624', 'base' => 38, 'pack' => 1, 'step' => 0.3,
                'sizes' => ['M12 × 300 mm', 'M16 × 400 mm', 'M20 × 500 mm', 'M24 × 600 mm'],
                'description' => 'J-type foundation bolts cast into concrete for columns, machines and poles. Supplied with nut and washer.',
                'specs' => ['Type' => 'J-hook', 'Includes' => '1 nut + 1 washer'],
            ],
            [
                'category' => 'nuts', 'name' => 'Hex Nut SS304', 'sku' => 'HN304', 'material' => 'Stainless Steel 304',
                'finish' => 'Plain', 'standard' => 'DIN 934', 'featured' => true, 'base' => 1.8, 'pack' => 100,
                'sizes' => ['M5', 'M6', 'M8', 'M10', 'M12', 'M16'],
                'description' => 'A2 stainless hexagon nuts, the everyday partner for SS bolts and threaded rods.',
                'specs' => ['Grade' => 'A2-70', 'Thread' => 'Metric coarse'],
            ],
            [
                'category' => 'nuts', 'name' => 'Nylock Nut (Nylon Insert Lock Nut)', 'sku' => 'NYL', 'material' => 'Mild Steel',
                'finish' => 'Zinc Plated', 'standard' => 'DIN 985', 'featured' => true, 'base' => 1.4, 'pack' => 100,
                'sizes' => ['M5', 'M6', 'M8', 'M10', 'M12'],
                'description' => 'Vibration-proof lock nuts with nylon ring. Ideal for vehicles, machines and moving assemblies.',
                'specs' => ['Locking' => 'Nylon insert', 'Temperature' => 'up to 120 °C'],
            ],
            [
                'category' => 'nuts', 'name' => 'Wing Nut', 'sku' => 'WN', 'material' => 'Mild Steel',
                'finish' => 'Zinc Plated', 'standard' => 'DIN 315', 'base' => 2.6, 'pack' => 50,
                'sizes' => ['M5', 'M6', 'M8', 'M10'],
                'description' => 'Hand-tightened wing nuts for quick-release fixtures, clamps and panels.',
            ],
            [
                'category' => 'nuts', 'name' => 'Flange Nut Serrated', 'sku' => 'FN', 'material' => 'Carbon Steel',
                'finish' => 'Zinc Plated', 'standard' => 'DIN 6923', 'base' => 1.9, 'pack' => 100,
                'sizes' => ['M6', 'M8', 'M10', 'M12'],
                'description' => 'Built-in washer face with serrations that bite and resist loosening. No separate washer needed.',
            ],
            [
                'category' => 'screws', 'name' => 'Self Drilling Roofing Screw with EPDM Washer', 'sku' => 'SDR', 'material' => 'Carbon Steel',
                'finish' => 'Ruspert Coated', 'standard' => 'DIN 7504K', 'featured' => true, 'base' => 2.2, 'pack' => 100,
                'sizes' => ['12-14 × 25 mm', '12-14 × 45 mm', '12-14 × 55 mm', '12-14 × 65 mm'],
                'description' => 'Hex-washer-head self-drilling screws for fixing roofing sheets to MS purlins. EPDM washer keeps water out.',
                'specs' => ['Drill point' => 'Tek #3', 'Washer' => 'EPDM bonded', 'Max steel thickness' => '5 mm'],
            ],
            [
                'category' => 'screws', 'name' => 'Drywall Screw (Bugle Head, Black)', 'sku' => 'DWS', 'material' => 'Hardened Steel',
                'finish' => 'Black Phosphate', 'base' => 0.45, 'pack' => 500,
                'sizes' => ['#6 × 25 mm', '#6 × 32 mm', '#6 × 38 mm', '#8 × 50 mm'],
                'description' => 'Fine-thread drywall screws for gypsum board on metal studs. Bugle head sits flush without tearing paper.',
            ],
            [
                'category' => 'screws', 'name' => 'Wood Screw CSK Phillips (Chipboard)', 'sku' => 'WS', 'material' => 'Carbon Steel',
                'finish' => 'Yellow Zinc', 'base' => 0.6, 'pack' => 200,
                'sizes' => ['3.5 × 16 mm', '4 × 25 mm', '4 × 40 mm', '5 × 50 mm', '5 × 70 mm'],
                'description' => 'Sharp-point, deep-thread countersunk screws for plywood, MDF and furniture assembly.',
            ],
            [
                'category' => 'screws', 'name' => 'Self Tapping Screw Pan Head SS', 'sku' => 'STP', 'material' => 'Stainless Steel 304',
                'finish' => 'Plain', 'standard' => 'DIN 7981', 'base' => 0.9, 'pack' => 200,
                'sizes' => ['#8 × 13 mm', '#8 × 19 mm', '#10 × 25 mm', '#10 × 38 mm'],
                'description' => 'Pan-head Phillips tapping screws for sheet metal, aluminium sections and electrical boxes.',
            ],
            [
                'category' => 'washers', 'name' => 'Plain Washer SS304', 'sku' => 'PW304', 'material' => 'Stainless Steel 304',
                'finish' => 'Plain', 'standard' => 'DIN 125', 'base' => 0.7, 'pack' => 200, 'hsn' => '7318',
                'sizes' => ['M5', 'M6', 'M8', 'M10', 'M12', 'M16'],
                'description' => 'Flat washers to spread load and protect surfaces under nuts and bolt heads.',
            ],
            [
                'category' => 'washers', 'name' => 'Spring Washer (Split Lock)', 'sku' => 'SW', 'material' => 'Spring Steel',
                'finish' => 'Zinc Plated', 'standard' => 'DIN 127', 'base' => 0.5, 'pack' => 200,
                'sizes' => ['M6', 'M8', 'M10', 'M12', 'M16'],
                'description' => 'Helical spring lock washers that keep tension on the joint and resist loosening.',
            ],
            [
                'category' => 'washers', 'name' => 'Fender Washer (Penny Washer)', 'sku' => 'FW', 'material' => 'Mild Steel',
                'finish' => 'Zinc Plated', 'standard' => 'DIN 9021', 'base' => 0.9, 'pack' => 100,
                'sizes' => ['M6 × 25 mm OD', 'M8 × 30 mm OD', 'M10 × 40 mm OD'],
                'description' => 'Large outer diameter washers for thin sheet, wood and soft materials.',
            ],
            [
                'category' => 'anchors', 'name' => 'Wedge Anchor Fastener (Through Bolt)', 'sku' => 'WA', 'material' => 'Carbon Steel',
                'finish' => 'Zinc Plated', 'featured' => true, 'base' => 14, 'pack' => 25, 'step' => 0.3,
                'sizes' => ['M8 × 75 mm', 'M10 × 90 mm', 'M12 × 100 mm', 'M12 × 150 mm', 'M16 × 150 mm'],
                'description' => 'Heavy-duty expansion anchors for fixing machines, racks, railings and structural brackets to concrete.',
                'specs' => ['Base material' => 'Concrete (M20+)', 'Includes' => 'Nut + washer', 'Install' => 'Drill same diameter as anchor'],
            ],
            [
                'category' => 'anchors', 'name' => 'Nylon Wall Plug (Gitti)', 'sku' => 'NWP', 'material' => 'Nylon 6',
                'finish' => 'Grey', 'base' => 0.35, 'pack' => 500, 'hsn' => '3926',
                'sizes' => ['6 mm', '8 mm', '10 mm', '12 mm'],
                'description' => 'Anti-rotation nylon plugs for brick, block and concrete. Use with wood screws.',
            ],
            [
                'category' => 'anchors', 'name' => 'Sleeve Anchor Hex Nut', 'sku' => 'SA', 'material' => 'Carbon Steel',
                'finish' => 'Zinc Plated', 'base' => 9.5, 'pack' => 50,
                'sizes' => ['M6 × 45 mm', 'M8 × 60 mm', 'M10 × 75 mm', 'M12 × 100 mm'],
                'description' => 'Versatile sleeve anchors for medium loads in concrete, brick and hollow block.',
            ],
            [
                'category' => 'rods', 'name' => 'Threaded Rod (Stud) MS Zinc', 'sku' => 'TR', 'material' => 'Mild Steel',
                'finish' => 'Zinc Plated', 'standard' => 'DIN 975', 'base' => 42, 'pack' => 1, 'step' => 0.35,
                'sizes' => ['M6 × 1 m', 'M8 × 1 m', 'M10 × 1 m', 'M12 × 1 m', 'M16 × 1 m'],
                'description' => 'Fully threaded 1-metre rods for ceiling hangers, cable trays, ducting and custom studs.',
            ],
            [
                'category' => 'rods', 'name' => 'Threaded Rod SS304', 'sku' => 'TR304', 'material' => 'Stainless Steel 304',
                'finish' => 'Plain', 'standard' => 'DIN 975', 'base' => 120, 'pack' => 1, 'step' => 0.4,
                'sizes' => ['M8 × 1 m', 'M10 × 1 m', 'M12 × 1 m'],
                'description' => 'Rust-free stainless studding for wet areas, food plants and exterior installations.',
            ],
            [
                'category' => 'rivets', 'name' => 'Blind Pop Rivet Aluminium', 'sku' => 'PR', 'material' => 'Aluminium',
                'finish' => 'Plain', 'standard' => 'DIN 7337', 'base' => 0.45, 'pack' => 500,
                'sizes' => ['3.2 × 8 mm', '4 × 10 mm', '4.8 × 12 mm', '4.8 × 16 mm'],
                'description' => 'Dome-head blind rivets with steel mandrel for aluminium windows, ducts and sheet metal.',
            ],
            [
                'category' => 'tools', 'name' => 'Combination Spanner Set (8 pcs, 6–22 mm)', 'sku' => 'SPS8', 'material' => 'Chrome Vanadium',
                'finish' => 'Satin Chrome', 'base' => 1150, 'pack' => 1, 'hsn' => '8204', 'featured' => true,
                'sizes' => ['8 pcs (6–22 mm)'],
                'description' => 'Drop-forged Cr-V spanners with open and ring ends. Comes with roll-up pouch.',
                'specs' => ['Sizes' => '6, 8, 10, 12, 14, 17, 19, 22 mm', 'Warranty' => '1 year'],
            ],
            [
                'category' => 'tools', 'name' => 'Allen Key Set Ball End (9 pcs)', 'sku' => 'AK9', 'material' => 'S2 Steel',
                'finish' => 'Black', 'base' => 380, 'pack' => 1, 'hsn' => '8204',
                'sizes' => ['Metric 1.5–10 mm'],
                'description' => 'Long-arm ball-end hex keys for angled access. Includes clip holder.',
            ],
            [
                'category' => 'tools', 'name' => 'HSS Drill Bit (Metal)', 'sku' => 'HSS', 'material' => 'HSS M2',
                'finish' => 'Titanium Coated', 'base' => 35, 'pack' => 1, 'step' => 0.28, 'hsn' => '8207',
                'sizes' => ['3 mm', '5 mm', '6 mm', '8 mm', '10 mm', '12 mm'],
                'description' => 'Fully ground HSS twist drills for steel, stainless and aluminium. 135° split point.',
            ],
        ];
    }
}
