<style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, 'Segoe UI', 'Inter', Roboto, sans-serif;
            margin: 0; padding: 24px; color: #1e293b; background: #eef2f7;
        }
        .sheet {
            max-width: 760px; margin: 0 auto; background: #fff;
            border-radius: 20px; overflow: hidden; box-shadow: 0 1px 3px rgba(15,23,42,.08);
        }

        .topbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 18px 24px; background: linear-gradient(120deg, #4f46e5, #6366f1);
            color: #fff;
        }
        .topbar .brand { display: flex; align-items: center; gap: 10px; }
        .topbar .brand img { height: 30px; object-fit: contain; background: #fff; border-radius: 6px; padding: 2px; }
        .topbar .brand strong { display: block; font-size: 13px; font-weight: 800; letter-spacing: -0.01em; }
        .topbar .brand span { display: block; font-size: 9.5px; color: #e0e7ff; }
        .topbar .doc-badge { text-align: right; }
        .topbar .doc-label {
            display: inline-block; background: rgba(255,255,255,.18); padding: 3px 10px;
            border-radius: 999px; font-size: 9.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em;
        }
        .topbar .doc-code { display: block; margin-top: 5px; font-size: 12px; font-weight: 700; letter-spacing: 0.02em; }

        
        .hero { display: flex; gap: 18px; padding: 20px 24px; border-bottom: 1px solid #e2e8f0; align-items: flex-start; }
        .hero-thumb {
            flex: 0 0 auto; min-width: 160px; min-height: 160px;
            max-width: 320px; max-height: 320px; border-radius: 16px; overflow: hidden;
            background: #f8fafc; border: 1.5px dashed #c7d2fe;
            display: flex; align-items: center; justify-content: center;
        }
        .hero-thumb img { display: block; max-width: 320px; max-height: 320px; width: auto; height: auto; }
        .hero-thumb .placeholder { color: #a5b4fc; font-size: 10.5px; text-align: center; padding: 0 12px; }

        .hero-info { flex: 1; min-width: 0; }
        .hero-info h1 { margin: 0 0 4px; font-size: 19px; font-weight: 800; color: #0f172a; letter-spacing: -0.01em; }
        .hero-info .sub { margin: 0 0 14px; font-size: 11px; color: #6366f1; font-weight: 700; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 16px; }
        .info-grid .item span { display: block; font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; margin-bottom: 2px; }
        .info-grid .item strong { font-size: 12px; font-weight: 700; color: #1e293b; }
        .info-grid .item strong.accent { color: #4f46e5; font-size: 14px; }

        .keterangan-box { margin: 16px 24px 0; padding: 12px 16px; background: #f8fafc; border-left: 3px solid #6366f1; border-radius: 0 12px 12px 0; }
        .keterangan-box p { margin: 0 0 3px; font-size: 9px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.06em; }
        .keterangan-box pre { margin: 0; font-family: inherit; font-size: 11px; white-space: pre-wrap; color: #334155; line-height: 1.6; }

        .section { padding: 18px 24px 4px; }
        .section-title {
            display: flex; align-items: center; gap: 8px; margin: 0 0 12px;
            font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #0f172a;
        }
        .section-title::before { content: ''; width: 4px; height: 14px; background: #4f46e5; border-radius: 3px; display: inline-block; }
        .section-title .note { font-size: 9.5px; font-weight: 600; text-transform: none; letter-spacing: 0; color: #94a3b8; }

        .size-chips { display: flex; flex-wrap: wrap; gap: 8px; padding-bottom: 4px; }
        .size-chips .chip {
            display: flex; align-items: stretch; border-radius: 10px; overflow: hidden;
            border: 1px solid #e2e8f0; min-width: 56px;
        }
        .size-chips .chip .chip-size {
            padding: 6px 10px; background: #f1f5f9; font-size: 11px; font-weight: 800; color: #475569;
            display: flex; align-items: center;
        }
        .size-chips .chip .chip-qty {
            padding: 6px 12px; background: #eef2ff; font-size: 13px; font-weight: 800; color: #4338ca;
            display: flex; align-items: center; justify-content: center; min-width: 26px;
        }
        .size-chips .chip-total {
            display: flex; align-items: center; gap: 8px; margin-left: 4px; padding: 6px 14px;
            background: #0f172a; border-radius: 10px; color: #fff; font-size: 11px; font-weight: 800; letter-spacing: 0.03em;
        }
        .empty-note { font-size: 10.5px; color: #94a3b8; font-style: italic; }

        table.roster, table.material { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        table.roster th, table.material th {
            background: #f8fafc; font-size: 9px; font-weight: 800; color: #94a3b8; text-transform: uppercase;
            letter-spacing: 0.05em; text-align: left; padding: 7px 8px; border-bottom: 1.5px solid #e2e8f0;
        }
        table.roster td, table.material td { padding: 7px 8px; font-size: 11px; border-bottom: 1px solid #f1f5f9; color: #334155; }
        table.roster tr:nth-child(even) td, table.material tr:nth-child(even) td { background: #fafbff; }
        table.roster td.num, table.material td.num { text-align: right; font-weight: 700; color: #4f46e5; }

        .footer-strip { display: flex; align-items: center; gap: 8px; margin: 6px 24px 0; padding: 10px 14px; background: #fffbeb; border-radius: 10px; }
        .footer-strip .dot { width: 6px; height: 6px; border-radius: 50%; background: #f59e0b; flex-shrink: 0; }
        .footer-strip span { font-size: 10px; color: #92400e; font-weight: 600; }

        .signature { display: flex; padding: 26px 24px 22px; gap: 20px; }
        .signature > div { flex: 1; text-align: center; }
        .signature p.role { font-size: 9.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; margin: 0 0 46px; }
        .signature .line { border-top: 1.5px solid #cbd5e1; width: 75%; margin: 0 auto; }

        .no-print { text-align: center; margin-top: 20px; }
        .no-print button { padding: 10px 28px; font-family: inherit; font-weight: 700; cursor: pointer; background: #4f46e5; color: #fff; border: none; border-radius: 10px; font-size: 13px; }
        .no-print button:hover { background: #4338ca; }

        @media screen and (max-width: 640px) {
            body { padding: 10px; }
            .hero { flex-direction: column; }
            .hero-thumb { flex: none; width: 100%; max-width: 100%; max-height: 220px; }
            table.roster, table.material { display: block; overflow-x: auto; white-space: nowrap; }
        }
        @media print {
            body { background: #fff; padding: 0; }
            .sheet { border-radius: 0; box-shadow: none; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
