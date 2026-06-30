zap-link/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   └── LinkController.php
│   │   │   ├── Web/
│   │   │   │   └── LinkController.php
│   │   │   └── Auth/
│   │   ├── Middleware/
│   │   │   ├── RateLimit.php
│   │   │   └── ValidateUrl.php
│   │   └── Requests/
│   │       ├── StoreLinkRequest.php
│   │       └── UpdateLinkRequest.php
│   ├── Models/
│   │   ├── Link.php
│   │   ├── Visit.php
│   │   └── User.php
│   ├── Services/
│   │   ├── LinkService.php
│   │   ├── UrlValidatorService.php
│   │   └── AnalyticsService.php
│   ├── Repositories/
│   │   ├── LinkRepository.php
│   │   └── VisitRepository.php
│   └── Events/
│       ├── LinkCreated.php
│       └── LinkVisited.php
├── database/
│   ├── migrations/
│   │   ├── create_links_table.php
│   │   └── create_visits_table.php
│   └── factories/
│       └── LinkFactory.php
├── routes/
│   ├── web.php
│   └── api.php
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   ├── components/
│   │   ├── links/
│   │   │   ├── index.blade.php
│   │   │   ├── create.blade.php
│   │   │   └── show.blade.php
│   │   └── dashboard/
│   └── css/
│       └── app.css
├── tests/
│   ├── Feature/
│   │   └── LinkTest.php
│   └── Unit/
│       └── LinkServiceTest.php
└── config/
    └── zap-link.php