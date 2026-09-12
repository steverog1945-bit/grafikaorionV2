{{--
    CSS bersama untuk semua dokumen print (nota, closing kas, SPK) -
    sebelumnya CSS identik ini copy-paste di 3 file terpisah (rawan
    diperbaiki di satu tempat tapi lupa di tempat lain). Include sekali per
    halaman SETELAH variabel $format ('thermal'/lainnya) sudah didefinisikan
    di controller, karena beberapa ukuran di sini bergantung padanya.
--}}
<style>
    * { box-sizing: border-box; }
    body {
        font-family: -apple-system, 'Segoe UI', 'Inter', Roboto, sans-serif;
        margin: 0; padding: 24px; color: #1e293b; background: #fff;
    }
    .sheet { max-width: {{ $format === 'thermal' ? '58mm' : '480px' }}; margin: 0 auto; }

    .print-header { text-align: center; margin-bottom: 14px; }
    .print-header img { max-height: {{ $format === 'thermal' ? '32px' : '44px' }}; margin-bottom: 6px; object-fit: contain; }
    .print-header h1 {
        font-size: {{ $format === 'thermal' ? '13px' : '19px' }};
        margin: 0; font-weight: 800; letter-spacing: -0.02em; color: #0f172a;
    }
    .print-header .company-info {
        font-size: {{ $format === 'thermal' ? '9px' : '11px' }};
        color: #64748b; margin: 2px 0 0; line-height: 1.4;
    }
    .print-header .doc-title {
        font-size: {{ $format === 'thermal' ? '10px' : '12px' }};
        text-transform: uppercase; letter-spacing: 0.08em; color: #4f46e5;
        font-weight: 700; margin: 6px 0 0;
    }

    .center { text-align: center; }
    .sub { font-size: 11px; color: #64748b; margin: 0 0 12px; }

    table { width: 100%; border-collapse: collapse; font-size: {{ $format === 'thermal' ? '10px' : '12.5px' }}; }
    th, td { padding: 6px 4px; text-align: left; vertical-align: top; }
    th {
        border-bottom: 1.5px solid #e2e8f0; font-size: {{ $format === 'thermal' ? '9px' : '10px' }};
        text-transform: uppercase; letter-spacing: 0.04em; color: #64748b; font-weight: 700;
    }
    td.right, th.right { text-align: right; }

    .divider { border-top: 1px dashed #cbd5e1; margin: 10px 0; }

    .total-row td { font-weight: 800; font-size: {{ $format === 'thermal' ? '11px' : '15px' }}; color: #0f172a; }

    .status-badge {
        display: inline-block; padding: 4px 12px; border-radius: 999px;
        background: #eef2ff; color: #4f46e5; font-weight: 800;
        font-size: {{ $format === 'thermal' ? '10px' : '12px' }};
    }

    .catatan {
        border: 1px dashed #cbd5e1; border-radius: 8px; padding: 10px; margin-top: 8px;
        font-size: {{ $format === 'thermal' ? '10px' : '11px' }}; background: #f8fafc;
    }

    .signature { display: flex; justify-content: space-between; margin-top: 36px; }
    .signature div { text-align: center; width: 45%; }
    .signature .line { border-top: 1px solid #334155; margin-top: 40px; padding-top: 6px; font-size: 11px; color: #475569; }

    .selisih-negatif { color: #dc2626; }
    .selisih-positif { color: #16a34a; }

    .footer-note { text-align: center; font-size: 10px; color: #94a3b8; margin-top: 16px; }

    .no-print { text-align: center; margin-top: 24px; }
    .no-print button {
        padding: 10px 28px; font-family: inherit; font-weight: 700; cursor: pointer;
        background: #4f46e5; color: #fff; border: none; border-radius: 10px; font-size: 13px;
    }
    .no-print button:hover { background: #4338ca; }

    @media print {
        .no-print { display: none !important; }
        body { padding: 0; }
    }
</style>
