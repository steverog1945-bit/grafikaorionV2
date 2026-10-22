<?php

// API eksternal (Android/iOS/integrasi) belum siap dipakai operasional.
// Secara default OFF agar tidak mengekspos data dan jalur transaksi yang
// masih menunggu audit RBAC, rate limiting, dan kesetaraan checkout.
return [
    'enabled' => (bool) env('ORION_PUBLIC_API_ENABLED', false),
];
