<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: "Hiragino Kaku Gothic Pro", "Meiryo", sans-serif; font-size: 14px; color: #333; line-height: 1.8; background: #f5f5f5; }
        .wrapper { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; }
        .header { background: #1e293b; color: #fff; padding: 20px 30px; }
        .header h1 { font-size: 16px; font-weight: normal; margin: 0; letter-spacing: 0.05em; }
        .body { padding: 30px; }
        .greeting { margin-bottom: 20px; }
        .summary-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px 20px; margin: 20px 0; }
        .summary-box table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .summary-box td { padding: 4px 0; }
        .summary-box td:last-child { text-align: right; font-weight: bold; }
        .total-row td { font-size: 16px; color: #0f172a; border-top: 1px solid #cbd5e1; padding-top: 8px; margin-top: 4px; }
        .note { font-size: 12px; color: #64748b; margin-top: 20px; padding-top: 16px; border-top: 1px solid #e2e8f0; }
        .footer { background: #f1f5f9; color: #64748b; font-size: 11px; padding: 16px 30px; text-align: center; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>請求書のご送付</h1>
    </div>
    <div class="body">
        <div class="greeting">
            <p>{{ $invoice->customer->name }} 御中</p>
            @if ($invoice->customer->contact_person)
                <p>{{ $invoice->customer->contact_person }} 様</p>
            @endif
        </div>

        <p>平素より大変お世話になっております。<br>
        株式会社サンプル商事です。</p>

        <p>下記の通り、請求書をお送りします。<br>
        添付ファイルをご確認の上、期日までにお支払いくださいますようお願い申し上げます。</p>

        <div class="summary-box">
            <table>
                <tr>
                    <td>請求書番号</td>
                    <td>{{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <td>発行日</td>
                    <td>{{ $invoice->issue_date->format('Y年m月d日') }}</td>
                </tr>
                <tr>
                    <td>支払期限</td>
                    <td><strong>{{ $invoice->due_date->format('Y年m月d日') }}</strong></td>
                </tr>
                <tr>
                    <td>対応受注番号</td>
                    <td>{{ $invoice->order->order_number }}</td>
                </tr>
                <tr class="total-row">
                    <td>ご請求金額（税込）</td>
                    <td>¥{{ number_format($invoice->total_amount) }}</td>
                </tr>
            </table>
        </div>

        @if ($invoice->notes)
            <p><strong>備考：</strong><br>{{ $invoice->notes }}</p>
        @endif

        <p style="margin-top:20px;">
            お振込先：〇〇銀行 〇〇支店 普通 1234567 カ）サンプルショウジ<br>
            ※ 振込手数料はご負担をお願いいたします。
        </p>

        <div class="note">
            ご不明な点がございましたら、下記担当者までお気軽にお問い合わせください。<br>
            本メールへの返信、またはお電話（03-0000-0000）にてご連絡ください。
        </div>
    </div>
    <div class="footer">
        株式会社サンプル商事 | 〒100-0001 東京都千代田区1-1-1 | TEL: 03-0000-0000
    </div>
</div>
</body>
</html>
