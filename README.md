# 受注・請求書発行システム

Laravel 11 製の受注管理 → 請求書発行システムです。

## 機能一覧

- **ダッシュボード** — 月次売上グラフ・KPI・直近受注一覧
- **受注管理** — 受注登録・編集・ステータス管理（見積/受注/出荷/完了/キャンセル）
- **請求書発行** — 受注から自動生成・PDF出力（DomPDF）
- **メール通知** — 請求書PDF添付メール送信（Laravel Queue対応）

## 必要環境

- PHP 8.2+
- Composer 2.x
- MySQL 8.0+
- Node.js 20+ / npm

## セットアップ

```bash
# 1. 依存パッケージインストール
composer install
npm install && npm run build

# 2. 環境設定
cp .env.example .env
php artisan key:generate

# 3. DB設定（.envを編集）
DB_DATABASE=order_system
DB_USERNAME=root
DB_PASSWORD=secret

# メール設定（.envを編集）
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_FROM_ADDRESS=noreply@yourcompany.com
MAIL_FROM_NAME="受注システム"

# 4. マイグレーション＆シーダー
php artisan migrate --seed

# 5. ストレージリンク
php artisan storage:link

# 6. Queueワーカー起動（メール送信用）
php artisan queue:work

# 7. 開発サーバー起動
php artisan serve
```

## 追加パッケージ

```bash
composer require barryvdh/laravel-dompdf
```

## ディレクトリ構成

```
app/
  Http/Controllers/
    DashboardController.php   ダッシュボード
    OrderController.php       受注管理 CRUD
    InvoiceController.php     請求書生成・PDF
  Mail/
    InvoiceMail.php           請求書メール
  Models/
    Customer.php
    Order.php
    OrderItem.php
    Invoice.php
    Product.php
  Services/
    InvoiceService.php        請求書生成ロジック
    DashboardService.php      集計ロジック

database/migrations/          マイグレーションファイル群
resources/views/              Bladeテンプレート群
```

## ステータスフロー

```
見積 (quote) → 受注 (ordered) → 出荷中 (shipping) → 完了 (completed)
                    ↓
               キャンセル (cancelled)
```
