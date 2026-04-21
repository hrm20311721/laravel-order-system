<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── 管理ユーザー ───
        User::create([
            'name'     => '管理者',
            'email'    => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        // ─── 顧客 ───
        $customers = [
            ['code' => 'C001', 'name' => '株式会社テック東京', 'email' => 'info@tech-tokyo.example.com', 'prefecture' => '東京都', 'address' => '新宿区西新宿1-1-1', 'contact_person' => '山田 太郎'],
            ['code' => 'C002', 'name' => '大阪商事株式会社',   'email' => 'purchase@osaka-shoji.example.com', 'prefecture' => '大阪府', 'address' => '大阪市北区梅田1-2-3', 'contact_person' => '田中 花子'],
            ['code' => 'C003', 'name' => '名古屋製造株式会社', 'email' => 'order@nagoya-mfg.example.com', 'prefecture' => '愛知県', 'address' => '名古屋市中村区名駅3-1-1', 'contact_person' => '鈴木 一郎'],
            ['code' => 'C004', 'name' => '福岡リテール合同会社', 'email' => null, 'prefecture' => '福岡県', 'address' => '福岡市博多区博多駅前1-1-1', 'contact_person' => '高橋 三郎'],
        ];

        $customerModels = array_map(fn($c) => Customer::create(array_merge($c, ['zip_code' => '100-0001'])), $customers);

        // ─── 商品マスタ ───
        $products = [
            ['code' => 'P001', 'name' => 'Webサイト制作（標準）',      'unit_price' => 500000, 'unit' => '式', 'tax_rate' => 10],
            ['code' => 'P002', 'name' => 'ECサイト構築',               'unit_price' => 1200000, 'unit' => '式', 'tax_rate' => 10],
            ['code' => 'P003', 'name' => 'システム保守（月額）',        'unit_price' => 50000,  'unit' => '月', 'tax_rate' => 10],
            ['code' => 'P004', 'name' => 'デザイン制作（ページ単位）',  'unit_price' => 30000,  'unit' => 'P',  'tax_rate' => 10],
            ['code' => 'P005', 'name' => 'コンサルティング（時間単位）', 'unit_price' => 15000,  'unit' => 'h',  'tax_rate' => 10],
            ['code' => 'P006', 'name' => 'サーバー費用',               'unit_price' => 20000,  'unit' => '月', 'tax_rate' => 10],
        ];

        $productModels = array_map(fn($p) => Product::create($p), $products);

        // ─── 受注データ（過去6ヶ月分） ───
        $statuses = ['ordered', 'shipping', 'completed', 'completed', 'completed'];

        foreach ($customerModels as $ci => $customer) {
            foreach (range(5, 0) as $monthsAgo) {
                if (rand(0, 2) === 0) continue; // ランダムにスキップ

                $orderDate = now()->subMonths($monthsAgo)->startOfMonth()->addDays(rand(0, 25));
                $status    = $statuses[array_rand($statuses)];
                if ($monthsAgo === 0) $status = 'ordered'; // 今月は受注中

                $order = Order::create([
                    'order_number'  => 'ORD-' . $orderDate->format('Ym') . '-' . str_pad(($ci + 1) * 10 + $monthsAgo, 4, '0', STR_PAD_LEFT),
                    'customer_id'   => $customer->id,
                    'status'        => $status,
                    'order_date'    => $orderDate,
                    'delivery_date' => $orderDate->copy()->addWeeks(2),
                    'notes'         => null,
                    'created_by'    => 1,
                    'subtotal'      => 0,
                    'tax_amount'    => 0,
                    'total_amount'  => 0,
                ]);

                // 明細を2〜3行追加
                $selectedProducts = array_slice($productModels, array_rand($productModels), rand(2, 3));
                foreach (array_values($selectedProducts) as $si => $product) {
                    $qty  = rand(1, 5);
                    $sub  = $product->unit_price * $qty;
                    $tax  = (int) floor($sub * $product->tax_rate / 100);
                    OrderItem::create([
                        'order_id'      => $order->id,
                        'product_id'    => $product->id,
                        'product_name'  => $product->name,
                        'unit_price'    => $product->unit_price,
                        'unit'          => $product->unit,
                        'quantity'      => $qty,
                        'tax_rate'      => $product->tax_rate,
                        'line_subtotal' => $sub,
                        'line_tax'      => $tax,
                        'line_total'    => $sub + $tax,
                        'sort_order'    => $si,
                    ]);
                }

                $order->recalculateTotals();
            }
        }

        $this->command->info('シードデータを作成しました。ログイン: admin@example.com / password');
    }
}
