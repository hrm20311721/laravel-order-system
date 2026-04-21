<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>請求書 {{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: "IPAexGothic", "Hiragino Kaku Gothic Pro", "Meiryo", sans-serif;
            font-size: 11pt;
            color: #1a1a1a;
            line-height: 1.5;
        }
        .page {
            padding: 32px 40px;
        }
        /* ─── ヘッダー ─── */
        .doc-title {
            font-size: 22pt;
            font-weight: bold;
            letter-spacing: 0.2em;
            text-align: center;
            border-bottom: 3px solid #1a1a1a;
            padding-bottom: 8px;
            margin-bottom: 20px;
        }
        .header-grid {
            display: table;
            width: 100%;
            margin-bottom: 24px;
        }
        .header-left, .header-right {
            display: table-cell;
            vertical-align: top;
            width: 50%;
        }
        .header-right { text-align: right; }
        /* ─── 顧客情報 ─── */
        .customer-name {
            font-size: 14pt;
            font-weight: bold;
            border-bottom: 1px solid #1a1a1a;
            padding-bottom: 4px;
            margin-bottom: 4px;
        }
        /* ─── 発行者情報 ─── */
        .issuer {
            font-size: 9pt;
            color: #444;
            line-height: 1.7;
        }
        .issuer strong { color: #1a1a1a; font-size: 11pt; }
        /* ─── 金額ボックス ─── */
        .amount-box {
            border: 2px solid #1a1a1a;
            padding: 10px 18px;
            display: inline-block;
            margin: 16px 0;
        }
        .amount-box .label { font-size: 9pt; color: #555; }
        .amount-box .value { font-size: 20pt; font-weight: bold; }
        /* ─── 請求情報 ─── */
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 9.5pt; }
        .meta-table td { padding: 3px 6px; }
        .meta-table td:first-child { color: #666; width: 90px; }
        /* ─── 明細テーブル ─── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            margin-bottom: 12px;
        }
        .items-table th {
            background: #1a1a1a;
            color: #fff;
            padding: 6px 8px;
            text-align: center;
            font-weight: normal;
            font-size: 9pt;
        }
        .items-table th.left  { text-align: left; }
        .items-table th.right { text-align: right; }
        .items-table td {
            padding: 7px 8px;
            border-bottom: 0.5px solid #ddd;
        }
        .items-table td.right { text-align: right; }
        .items-table td.center { text-align: center; }
        .items-table tr:nth-child(even) td { background: #f8f8f8; }
        /* ─── 合計欄 ─── */
        .totals-table { width: 260px; margin-left: auto; border-collapse: collapse; font-size: 10pt; }
        .totals-table td { padding: 4px 10px; }
        .totals-table td:last-child { text-align: right; }
        .totals-table .grand { font-size: 12pt; font-weight: bold; border-top: 2px solid #1a1a1a; }
        /* ─── 備考 ─── */
        .notes-box {
            border: 0.5px solid #ccc;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 9pt;
            color: #444;
            margin-top: 20px;
            min-height: 50px;
        }
        .notes-box .notes-label { font-weight: bold; margin-bottom: 4px; color: #1a1a1a; }
        /* ─── フッター ─── */
        .footer { text-align: center; font-size: 8pt; color: #999; margin-top: 30px; }
    </style>
</head>
<body>
<div class="page">

    {{-- タイトル --}}
    <div class="doc-title">請 求 書</div>

    {{-- ヘッダー2カラム --}}
    <div class="header-grid">
        {{-- 左：宛先 --}}
        <div class="header-left">
            <div class="customer-name">{{ $invoice->customer->name }} 御中</div>
            @if ($invoice->customer->contact_person)
                <div style="font-size:9.5pt;color:#555;margin-top:2px;">
                    ご担当：{{ $invoice->customer->contact_person }} 様
                </div>
            @endif
            <div style="font-size:9pt;color:#555;margin-top:8px;">
                {{ $invoice->customer->full_address }}
            </div>
        </div>
        {{-- 右：発行者 --}}
        <div class="header-right">
            <div class="issuer">
                <strong>株式会社サンプル商事</strong><br>
                〒100-0001 東京都千代田区1-1-1<br>
                TEL: 03-0000-0000<br>
                FAX: 03-0000-0001<br>
                登録番号：T1234567890123
            </div>
        </div>
    </div>

    {{-- 合計金額ボックス --}}
    <div class="amount-box">
        <div class="label">ご請求金額（税込）</div>
        <div class="value">¥{{ number_format($invoice->total_amount) }}-</div>
    </div>

    {{-- 請求メタ情報 --}}
    <table class="meta-table">
        <tr>
            <td>請求書番号</td>
            <td>{{ $invoice->invoice_number }}</td>
            <td style="padding-left:30px;">発行日</td>
            <td>{{ $invoice->issue_date->format('Y年m月d日') }}</td>
        </tr>
        <tr>
            <td>受注番号</td>
            <td>{{ $invoice->order->order_number }}</td>
            <td style="padding-left:30px;">支払期限</td>
            <td><strong>{{ $invoice->due_date->format('Y年m月d日') }}</strong></td>
        </tr>
    </table>

    {{-- 明細テーブル --}}
    <table class="items-table">
        <thead>
            <tr>
                <th class="left" style="width:38%">品名</th>
                <th style="width:12%">数量</th>
                <th style="width:10%">単位</th>
                <th class="right" style="width:14%">単価</th>
                <th style="width:8%">税率</th>
                <th class="right" style="width:18%">金額（税抜）</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td class="center">{{ $item->quantity }}</td>
                    <td class="center">{{ $item->unit }}</td>
                    <td class="right">¥{{ number_format($item->unit_price) }}</td>
                    <td class="center">{{ $item->tax_rate }}%</td>
                    <td class="right">¥{{ number_format($item->line_subtotal) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- 合計 --}}
    <table class="totals-table">
        <tr>
            <td style="color:#555;">小計（税抜）</td>
            <td>¥{{ number_format($invoice->subtotal) }}</td>
        </tr>
        <tr>
            <td style="color:#555;">消費税（10%）</td>
            <td>¥{{ number_format($invoice->tax_amount) }}</td>
        </tr>
        <tr class="grand">
            <td>合計（税込）</td>
            <td>¥{{ number_format($invoice->total_amount) }}</td>
        </tr>
    </table>

    {{-- 備考 --}}
    <div class="notes-box">
        <div class="notes-label">備考</div>
        <div>{{ $invoice->notes ?? '下記口座へお振込ください。振込手数料はご負担をお願いいたします。' }}</div>
        <div style="margin-top:6px;">振込先：〇〇銀行 〇〇支店 普通 1234567 カ）サンプルショウジ</div>
    </div>

    <div class="footer">
        本請求書に関するお問い合わせは、担当営業までご連絡ください。
    </div>

</div>
</body>
</html>
