# Graph Report - ngebadmintonyuk  (2026-09-11)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 1665 nodes · 2568 edges · 172 communities (138 shown, 34 thin omitted)
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- Illuminate\Http\Request
- scripts
- composer.json
- PlaySession
- Illuminate\Database\Eloquent\Model
- package.json
- laravel-best-practices/SKILL.md
- Illuminate\Database\Migrations\Migration
- require-dev
- Illuminate\Foundation\Http\FormRequest
- command
- psr-4
- PushNotificationSender.php
- logging.php
- console.php
- app.js
- Illuminate\Database\Seeder
- Modul
- TransactionService
- Modul
- Database
- Modul
- Modul
- Modul
- Illuminate\Support\Str
- Modul
- 09-ui-guideline.md
- Controller
- Expense
- AGENTS.md
- Livewire Development
- Illuminate\Database\Eloquent\Relations\HasMany
- User
- Modul
- Illuminate\Validation\ValidationException
- PushNotification
- Pest 5 Features
- Illuminate\Database\Eloquent\Relations\BelongsTo
- TopUpRequestController.php
- PushSubscription
- Tailwind CSS Development
- StorePushSubscriptionRequest
- Status dan konteks proyek NgeBadmintonYuk — arsip
- Modul
- Detection Checklist
- Process
- Architecture Best Practices
- Security Best Practices
- Actions Models Http
- Laravel Boost Guidelines
- UpdateMembershipRequest
- ReportService
- Laravel Boost
- Advanced Query Patterns
- Database Performance Best Practices
- Events & Notifications Best Practices
- Queue & Job Best Practices
- Income
- AppServiceProvider.php
- Illuminate\View\View
- web.php
- Caching Best Practices
- Eloquent Best Practices
- Migration Best Practices
- require
- Blade & Views Best Practices
- Error Handling Best Practices
- Task Scheduling Best Practices
- Testing Best Practices
- ShuttlecockItem
- FirebaseCloudMessaging.php
- Collection Best Practices
- HTTP Client Best Practices
- Mail Best Practices
- Configuration Best Practices
- Routing & Controllers Best Practices
- Conventions & Style
- Validation & Forms Best Practices
- UpdateTopUpSettingRequest
- config
- Color System
- Typography Hierarchy
- Actions Models Services Http
- Dashboard saat ini — 7 September 2026
- Laporan saat ini — 7 September 2026
- SessionRegistration
- Actions Http
- Actions Models
- Controllers
- Jsviews
- 05-category.md
- Visual Signature
- Semantic Color
- Public Sessions
- Push Notifications
- autoload-dev
- extra
- keywords
- 10-project-status.md
- Anti AI-Slop Rules
- Button
- NgeKas — UI Guideline
- TopUpSetting
- Illuminate\Http\RedirectResponse
- Aturan operasional yang tidak boleh terlewat
- Konteks final proyek NgeBadmintonYuk
- Http
- UserFactory
- Acuan proyek saat ini — 7 September 2026
- Status branding — 7 September 2026
- Database saat ini — 7 September 2026
- Status proyek: rujukan dan arsip
- Pemasukan saat ini — 7 September 2026
- RecordAttendanceAction
- 07-expense.md
- Status UI — 7 September 2026
- StoreSessionRegistrationRequest
- Category
- FinanceTest.php
- UpdatePlaySessionRequest
- PushNotificationManager
- Auth
- StockMovement
- RegistrationController.php
- StoreStockMovementRequest
- FirebaseCloudMessaging
- bootstrap/app.php

## God Nodes (most connected - your core abstractions)
1. `User` - 119 edges
2. `PlaySession` - 72 edges
3. `SessionRegistration` - 45 edges
4. `Membership` - 40 edges
5. `PushSubscription` - 29 edges
6. `ShuttlecockItem` - 25 edges
7. `Category` - 20 edges
8. `Controller` - 20 edges
9. `TopUpRequest` - 20 edges
10. `Income` - 20 edges

## Surprising Connections (you probably didn't know these)
- `createMembership()` --references--> `User`  [EXTRACTED]
  tests/Feature/MembershipManagementTest.php → app/Models/User.php
- `send()` --references--> `PushSubscription`  [EXTRACTED]
  tests/Feature/PushNotificationTest.php → app/Models/PushSubscription.php
- `createMembership()` --calls--> `Membership`  [EXTRACTED]
  tests/Feature/MembershipManagementTest.php → app/Models/Membership.php
- `browser()` --calls--> `installMemberNotifications()`  [EXTRACTED]
  tests/JavaScript/member-notifications.test.js → resources/js/member-notifications.js
- `setup()` --calls--> `createLoadingController()`  [EXTRACTED]
  tests/JavaScript/server-loading.test.js → resources/js/server-loading.js

## Import Cycles
- None detected.

## Communities (172 total, 34 thin omitted)

### Community 1 - "scripts"
Cohesion: 0.06
Nodes (37): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+29 more)

### Community 2 - "composer.json"
Cohesion: 0.25
Nodes (7): description, license, minimum-stability, name, prefer-stable, $schema, type

### Community 3 - "PlaySession"
Cohesion: 0.11
Nodes (8): DeletePlaySessionAction, RegisterForPlaySessionAction, SendSessionRegistrationNotificationAction, UpdatePlaySessionAction, PlaySessionController, StorePlaySessionRequest, PlaySession, Throwable

### Community 4 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.31
Nodes (5): Illuminate\Database\Eloquent\Attributes\Fillable, Illuminate\Database\Eloquent\Attributes\Hidden, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Database\Eloquent\Model, Illuminate\Support\Carbon

### Community 5 - "package.json"
Cohesion: 0.06
Nodes (31): aislop, concurrently, firebase, @laravel/multiplex, laravel-vite-plugin, lightningcss-linux-x64-gnu, dependencies, concurrently (+23 more)

### Community 6 - "laravel-best-practices/SKILL.md"
Cohesion: 0.29
Nodes (5): Consistency First, Decision Rules, How to Apply, Laravel Best Practices, Rule Index

### Community 7 - "Illuminate\Database\Migrations\Migration"
Cohesion: 0.05
Nodes (3): Illuminate\Database\Migrations\Migration, Illuminate\Database\Schema\Blueprint, Illuminate\Support\Facades\Schema

### Community 8 - "require-dev"
Cohesion: 0.17
Nodes (12): require-dev, fakerphp/faker, larastan/larastan, laravel/boost, laravel/pail, laravel/pao, laravel/pint, laravel/sail (+4 more)

### Community 9 - "Illuminate\Foundation\Http\FormRequest"
Cohesion: 0.13
Nodes (7): StoreMembershipRequest, UpdateAttendanceRequest, UpdateMemberRequest, Illuminate\Contracts\Validation\ValidationRule, Illuminate\Foundation\Http\FormRequest, Illuminate\Validation\Rule, Illuminate\Validation\Validator

### Community 10 - "command"
Cohesion: 0.20
Nodes (9): command, enabled, type, mcp, laravel-boost, $schema, artisan, boost:mcp (+1 more)

### Community 11 - "psr-4"
Cohesion: 0.29
Nodes (7): autoload, files, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\, app/helpers.php

### Community 13 - "logging.php"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 22 - "app.js"
Cohesion: 0.11
Nodes (18): installButton, installGuide, menu, scoreboard, serverLoading, sidebar, installMemberNotifications(), isIosDevice() (+10 more)

### Community 36 - "Illuminate\Database\Seeder"
Cohesion: 0.10
Nodes (11): AttendanceSeeder, DatabaseSeeder, MembershipSeeder, MembershipTransactionSeeder, PlaySessionSeeder, ShuttlecockItemSeeder, StockMovementSeeder, TopUpRequestSeeder (+3 more)

### Community 37 - "Modul"
Cohesion: 0.04
Nodes (47): Amount, Business Rule, Category, Category, Contoh Data, Create Transaction, Currency Input, Database (+39 more)

### Community 39 - "Modul"
Cohesion: 0.05
Nodes (38): Business Rule, Daftar Pemasukan, Daftar Pengeluaran, Database, Default Periode, Empty Result, Empty State, End Date (+30 more)

### Community 40 - "Database"
Cohesion: 0.05
Nodes (37): Business Rule, categories, Category, Category Type, Database, Database Transaction, Date, Delete Rule (+29 more)

### Community 41 - "Modul"
Cohesion: 0.05
Nodes (37): Admin Seeder, Authenticated User, Business Rule, Database, Error State, Flow, Future Improvement, Guest (+29 more)

### Community 42 - "Modul"
Cohesion: 0.06
Nodes (34): Business Rule, Category Type, CategoryForm, CategoryIndex, Database, Default Category, Delete Category, Delete Confirmation (+26 more)

### Community 43 - "Modul"
Cohesion: 0.06
Nodes (32): Business Rule, Dashboard Read Only, Database, Empty State, Flow, Future Improvement, Header, Livewire Component (+24 more)

### Community 45 - "Modul"
Cohesion: 0.07
Nodes (30): Accent, Background, Brand Utama, Business Rule, Button, Card, Color Palette, Currency (+22 more)

### Community 46 - "09-ui-guideline.md"
Cohesion: 0.06
Nodes (30): Border, Border Radius, Brand Personality, Dashboard Layout, Design Direction, Design Test, Destructive Action, Dynamic Detail (+22 more)

### Community 47 - "Controller"
Cohesion: 0.40
Nodes (3): Controller, NotificationSetupController, Illuminate\Contracts\View\View

### Community 48 - "Expense"
Cohesion: 0.21
Nodes (3): Expense, ReportRepository, Illuminate\Database\Eloquent\Builder

### Community 49 - "AGENTS.md"
Cohesion: 0.15
Nodes (12): APIs & Eloquent Resources, Deployment, Do Things the Laravel Way, Laravel Pint Code Formatter, Livewire, Model Creation, Pest, PHP (+4 more)

### Community 50 - "Livewire Development"
Cohesion: 0.08
Nodes (24): Component-Scoped Interceptors, Intercept Messages, Intercept Requests, Interceptor System (v4), Livewire 4 JavaScript Integration, Magic Properties, Alpine & JavaScript, Basic Usage (+16 more)

### Community 52 - "User"
Cohesion: 0.07
Nodes (17): DeleteMemberAction, User, AttendanceFactory, MembershipFactory, MembershipTransactionFactory, PlaySessionFactory, PushNotificationFactory, PushSubscriptionFactory (+9 more)

### Community 53 - "Modul"
Cohesion: 0.10
Nodes (21): Business Rule, Database, Flow, Future Improvement, Livewire Component, Modul, Pemasukan, Pengeluaran (+13 more)

### Community 54 - "Illuminate\Validation\ValidationException"
Cohesion: 0.16
Nodes (7): CreateTopUpRequestAction, Illuminate\Database\QueryException, Illuminate\Http\UploadedFile, Illuminate\Support\Facades\DB, Illuminate\Support\Facades\Storage, Illuminate\Validation\ValidationException, RuntimeException

### Community 55 - "PushNotification"
Cohesion: 0.15
Nodes (4): PushNotificationSender, PushNotificationController, SendPushNotificationRequest, PushNotification

### Community 56 - "Pest 5 Features"
Cohesion: 0.10
Nodes (19): Architecture Testing, Assertions, Basic Test Structure, Basic Usage, Browser Test Example, Common Pitfalls, Creating Tests, Datasets (+11 more)

### Community 57 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.12
Nodes (6): ReviewTopUpRequestAction, ExpenseDetail, IncomeDetail, MembershipTransaction, TopUpRequest, Illuminate\Database\Eloquent\Relations\BelongsTo

### Community 58 - "TopUpRequestController.php"
Cohesion: 0.18
Nodes (4): TopUpRequestController, ReviewTopUpRequest, StoreTopUpRequest, Symfony\Component\HttpFoundation\StreamedResponse

### Community 59 - "PushSubscription"
Cohesion: 0.25
Nodes (5): send(), PushSubscription, Illuminate\Http\Client\Request, Illuminate\Support\Facades\Http, send()

### Community 60 - "Tailwind CSS Development"
Cohesion: 0.14
Nodes (13): Basic Usage, Common Patterns, Common Pitfalls, CSS-First Configuration, Dark Mode, Documentation, Flexbox Layout, Grid Layout (+5 more)

### Community 61 - "StorePushSubscriptionRequest"
Cohesion: 0.21
Nodes (5): PushSubscriptionController, DeletePushSubscriptionRequest, StorePushSubscriptionRequest, Illuminate\Http\JsonResponse, Illuminate\Http\Response

### Community 62 - "Status dan konteks proyek NgeBadmintonYuk — arsip"
Cohesion: 0.20
Nodes (10): Arsitektur aktual, Cara melanjutkan pekerjaan, Data dan aturan bisnis penting, Fitur yang sudah memiliki implementasi, Hal yang perlu dituntaskan atau diputuskan, Peta dokumentasi, Posisi proyek, Status dan konteks proyek NgeBadmintonYuk — arsip (+2 more)

### Community 63 - "Modul"
Cohesion: 0.04
Nodes (49): Amount, Business Rule, Category, Category, Contoh Data, Create Transaction, Currency Input, Database (+41 more)

### Community 64 - "Detection Checklist"
Cohesion: 0.17
Nodes (11): A. Validation & HTTP input, B. Controllers & routing, C. Authorization, D. Eloquent & models, Detection Checklist, E. Architecture & organization, F. Frontend & views, G. Database & migrations (+3 more)

### Community 65 - "Process"
Cohesion: 0.17
Nodes (11): Edge cases, Glob mapping, Ground Rules (read before you start), Infer Conventions, Process, Step 0: Orient, Step 1: Predefined sweep, Step 2: Open-ended pass (+3 more)

### Community 66 - "Architecture Best Practices"
Cohesion: 0.17
Nodes (11): Architecture Best Practices, Code to Interfaces, Convention Over Configuration, Default Sort by Descending, Single-Purpose Action Classes, Use Atomic Locks for Race Conditions, Use `Concurrency::run()` for Parallel Execution, Use `Context` for Request-Scoped Data (+3 more)

### Community 67 - "Security Best Practices"
Cohesion: 0.18
Nodes (11): Audit Dependencies, Authorize Every Action, CSRF Protection, Encrypt Sensitive Database Fields, Escape Output to Prevent XSS, Keep Secrets Out of Code, Mass Assignment Protection, Prevent SQL Injection (+3 more)

### Community 68 - "Actions Models Http"
Cohesion: 0.17
Nodes (11): Actions Models Http, Admin settings supersede fixed top up amount, Play-session capacity is enforced atomically, Play sessions include an ordered waiting list and linked income, Session lists are scoped and member-backed, Session registration requires an account, Session registration sanctions use normalized phone history, Top up bootstraps a community package (+3 more)

### Community 69 - "Laravel Boost Guidelines"
Cohesion: 0.22
Nodes (9): Application Structure & Architecture, Conventions, Documentation Files, Foundational Context, Frontend Bundling, Laravel Boost Guidelines, Replies, Skills Activation (+1 more)

### Community 72 - "ReportService"
Cohesion: 0.22
Nodes (4): DashboardController, ReportController, ReportService, Barryvdh\DomPDF\Facade\Pdf

### Community 73 - "Laravel Boost"
Cohesion: 0.29
Nodes (7): Artisan, Laravel Boost, Project Rules, Search Syntax, Searching Documentation (IMPORTANT), Tinker, Tools

### Community 74 - "Advanced Query Patterns"
Cohesion: 0.20
Nodes (9): Advanced Query Patterns, Create Dynamic Relationships via Subquery FK, Prefer `whereIn` + Subquery Over `whereHas`, Sometimes Two Simple Queries Beat One Complex Query, Use `addSelect()` Subqueries for Single Values from Has-Many, Use Compound Indexes Matching `orderBy` Column Order, Use Conditional Aggregates Instead of Multiple Count Queries, Use Correlated Subqueries for Has-Many Ordering (+1 more)

### Community 75 - "Database Performance Best Practices"
Cohesion: 0.20
Nodes (9): Add Database Indexes, Always Eager Load Relationships, Chunk Large Datasets, Database Performance Best Practices, No Queries in Blade Templates, Prevent Lazy Loading in Development, Select Only Needed Columns, Use `cursor()` for Memory-Efficient Iteration (+1 more)

### Community 76 - "Events & Notifications Best Practices"
Cohesion: 0.20
Nodes (9): Always Queue Notifications, Events & Notifications Best Practices, Implement `HasLocalePreference` on Notifiable Models, Rely on Event Discovery, Route Notification Channels to Dedicated Queues, Run `event:cache` in Production Deploy, Use `afterCommit()` on Notifications in Transactions, Use On-Demand Notifications for Non-User Recipients (+1 more)

### Community 77 - "Queue & Job Best Practices"
Cohesion: 0.18
Nodes (10): Always Implement `failed()`, Batch Related Jobs, Implement `ShouldBeUnique`, Queue & Job Best Practices, Rate Limit External API Calls in Jobs, `retryUntil()` Needs `$tries = 0`, Set `retry_after` Greater Than `timeout`, Use Exponential Backoff (+2 more)

### Community 78 - "Income"
Cohesion: 0.16
Nodes (3): Attendance, Income, Illuminate\Database\Eloquent\Relations\HasOne

### Community 79 - "AppServiceProvider.php"
Cohesion: 0.18
Nodes (7): AppServiceProvider, Carbon\CarbonImmutable, Illuminate\Support\Facades\Date, Illuminate\Support\Facades\Gate, Illuminate\Support\Facades\View, Illuminate\Support\ServiceProvider, Illuminate\Validation\Rules\Password

### Community 80 - "Illuminate\View\View"
Cohesion: 0.22
Nodes (4): MemberController, PublicPlaySessionController, FilterPlaySessionsRequest, Illuminate\View\View

### Community 81 - "web.php"
Cohesion: 0.15
Nodes (8): ResetLegacyPushSubscriptionsAction, AuthController, RequireCurrentPushSetup, RequireMemberNotifications, Closure, Illuminate\Support\Facades\Auth, Illuminate\Support\Facades\Route, Symfony\Component\HttpFoundation\Response

### Community 82 - "Caching Best Practices"
Cohesion: 0.22
Nodes (8): Caching Best Practices, Configure Failover Cache Stores in Production, Use `Cache::add()` for Atomic Conditional Writes, Use `Cache::flexible()` for Stale-While-Revalidate, Use `Cache::memo()` to Avoid Redundant Hits Within a Request, Use `Cache::remember()` Instead of Manual Get/Put, Use Cache Tags to Invalidate Related Groups, Use `once()` for Per-Request Memoization

### Community 83 - "Eloquent Best Practices"
Cohesion: 0.22
Nodes (8): Apply Global Scopes Sparingly, Avoid Hardcoded Table Names in Queries, Cast Date Columns Properly, Define Attribute Casts, Eloquent Best Practices, Use Correct Relationship Types, Use Local Scopes for Reusable Queries, Use `whereBelongsTo()` for Relationship Queries

### Community 84 - "Migration Best Practices"
Cohesion: 0.22
Nodes (8): Add Indexes in the Migration, Generate Migrations with Artisan, Keep Migrations Focused, Migration Best Practices, Mirror Defaults in Model `$attributes`, Never Modify Deployed Migrations, Use `constrained()` for Foreign Keys, Write Reversible `down()` Methods by Default

### Community 85 - "require"
Cohesion: 0.22
Nodes (9): require, barryvdh/laravel-dompdf, google/auth, laravel/framework, laravel/tinker, livewire/blaze, livewire/livewire, minishlink/web-push (+1 more)

### Community 86 - "Blade & Views Best Practices"
Cohesion: 0.25
Nodes (7): Blade & Views Best Practices, Prefer Blade Components Over `@include`, Use `$attributes->merge()` in Component Templates, Use `@aware` for Deeply Nested Component Props, Use Blade Fragments for Partial Re-Renders (htmx/Turbo), Use `@pushOnce` for Per-Component Scripts, Use View Composers for Shared View Data

### Community 87 - "Error Handling Best Practices"
Cohesion: 0.25
Nodes (7): Add Context to Exception Classes, Enable `dontReportDuplicates()`, Error Handling Best Practices, Exception Reporting and Rendering, Force JSON Error Rendering for API Routes, Throttle High-Volume Exceptions, Use `ShouldntReport` for Exceptions That Should Never Log

### Community 88 - "Task Scheduling Best Practices"
Cohesion: 0.25
Nodes (7): Task Scheduling Best Practices, Use `environments()` to Restrict Tasks, Use `onOneServer()` on Multi-Server Deployments, Use `runInBackground()` for Concurrent Long Tasks, Use Schedule Groups for Shared Configuration, Use `takeUntilTimeout()` for Time-Bounded Processing, Use `withoutOverlapping()` on Variable-Duration Tasks

### Community 89 - "Testing Best Practices"
Cohesion: 0.25
Nodes (7): Call `Event::fake()` After Factory Setup, Testing Best Practices, Use `Exceptions::fake()` to Assert Exception Reporting, Use Factory States and Sequences, Use `LazilyRefreshDatabase` Over `RefreshDatabase`, Use Model Assertions Over Raw Database Assertions, Use `recycle()` to Share Relationship Instances Across Factories

### Community 90 - "ShuttlecockItem"
Cohesion: 0.13
Nodes (5): DeleteShuttlecockItemAction, ShuttlecockInventoryController, StoreShuttlecockItemRequest, UpdateShuttlecockItemRequest, ShuttlecockItem

### Community 91 - "FirebaseCloudMessaging.php"
Cohesion: 0.20
Nodes (9): Google\Auth\Credentials\ServiceAccountCredentials, Illuminate\Http\Client\ConnectionException, Illuminate\Http\Client\PendingRequest, Illuminate\Http\Client\RequestException, Illuminate\Support\Arr, Illuminate\Support\Facades\Log, JsonException, Minishlink\WebPush\Subscription (+1 more)

### Community 92 - "Collection Best Practices"
Cohesion: 0.29
Nodes (6): Choose `cursor()` vs. `lazy()` Correctly, Collection Best Practices, Use `#[CollectedBy]` for Custom Collection Classes, Use Higher-Order Messages for Simple Operations, Use `lazyById()` When Updating Records While Iterating, Use `toQuery()` for Bulk Operations on Collections

### Community 93 - "HTTP Client Best Practices"
Cohesion: 0.29
Nodes (6): Always Set Explicit Timeouts, Fake HTTP Calls in Tests, Handle Errors Explicitly, HTTP Client Best Practices, Use Request Pooling for Concurrent Requests, Use Retry with Backoff for External APIs

### Community 94 - "Mail Best Practices"
Cohesion: 0.29
Nodes (6): Implement `ShouldQueue` on the Mailable Class, Mail Best Practices, Separate Content Tests from Sending Tests, Use `afterCommit()` on Mailables Inside Transactions, Use `assertQueued()` Not `assertSent()` for Queued Mailables, Use Markdown Mailables for Transactional Emails

### Community 95 - "Configuration Best Practices"
Cohesion: 0.33
Nodes (5): Configuration Best Practices, `env()` Only in Config Files, Use `App::environment()` for Environment Checks, Use Constants and Language Files, Use Encrypted Env or External Secrets

### Community 96 - "Routing & Controllers Best Practices"
Cohesion: 0.29
Nodes (6): Keep Controllers Thin, Routing & Controllers Best Practices, Type-Hint Form Requests, Use Implicit Route Model Binding, Use Resource Controllers, Use Scoped Bindings for Nested Resources

### Community 97 - "Conventions & Style"
Cohesion: 0.29
Nodes (6): Conventions & Style, Follow Laravel Naming Conventions, No Inline JS/CSS in Blade, No Unnecessary Comments, Prefer Shorter Readable Syntax, Use Laravel String & Array Helpers

### Community 98 - "Validation & Forms Best Practices"
Cohesion: 0.29
Nodes (6): Always Use `validated()`, Array vs. String Notation for Rules, Use Form Request Classes, Use `Rule::when()` for Conditional Validation, Use the `after()` Method for Custom Validation, Validation & Forms Best Practices

### Community 101 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 103 - "Color System"
Cohesion: 0.33
Nodes (6): Accent, Background, Color System, Primary, Surface, Text

### Community 104 - "Typography Hierarchy"
Cohesion: 0.33
Nodes (6): Body, Hero Number, Label, Page Title, Section Title, Typography Hierarchy

### Community 105 - "Actions Models Services Http"
Cohesion: 0.29
Nodes (6): Actions Models Services Http, No refund workflow, Push notifications are manual and synchronous, Session activity uses standard Web Push, Session activity uses standard Web Push, Top up cash and session quota settle without duplicate income

### Community 108 - "SessionRegistration"
Cohesion: 0.08
Nodes (12): CreateSessionRegistrationByAdminAction, DeleteSessionRegistrationAction, MarkSessionRegistrationPresentAction, RecordSessionRegistrationPaymentAction, UpdateSessionRegistrationAction, SessionRegistrationController, CancelSessionRegistrationRequest, MarkSessionRegistrationPresentRequest (+4 more)

### Community 109 - "Actions Http"
Cohesion: 0.50
Nodes (3): Actions Http, Legacy FCM requires one-time reinstall, Players may cancel eligible registrations

### Community 110 - "Actions Models"
Cohesion: 0.50
Nodes (3): Actions Models, Membership credits use a ledger, Shuttlecock stock uses movements

### Community 111 - "Controllers"
Cohesion: 0.50
Nodes (3): Controllers, Member dashboard shows joined sessions only, Session indexes require month selection

### Community 112 - "Jsviews"
Cohesion: 0.50
Nodes (3): Jsviews, Keep mobile navigation actions reachable, Scoreboard remains device-local

### Community 114 - "Visual Signature"
Cohesion: 0.50
Nodes (4): 1. Cobalt Hero, 2. Yellow Motion Accent, 3. Editorial Transaction List, Visual Signature

### Community 115 - "Semantic Color"
Cohesion: 0.50
Nodes (4): Expense, Income, Semantic Color, Warning

### Community 118 - "autoload-dev"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

### Community 119 - "extra"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

### Community 120 - "keywords"
Cohesion: 0.67
Nodes (3): keywords, framework, laravel

### Community 121 - "10-project-status.md"
Cohesion: 0.15
Nodes (3): Project Rules Index, Arsip rancangan autentikasi awal, Autentikasi saat ini — 7 September 2026

### Community 148 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.14
Nodes (8): AdjustMembershipCreditAction, DeleteMembershipAction, GrantMembershipAction, MembershipController, AdjustMembershipCreditRequest, Membership, Illuminate\Http\RedirectResponse, createMembership()

### Community 150 - "Aturan operasional yang tidak boleh terlewat"
Cohesion: 0.22
Nodes (9): Aturan operasional yang tidak boleh terlewat, Backlog yang tetap terbuka, Inventaris fitur, Inventori, push dan akses, Matriks fitur dan keselarasan proyek, Membership dan top-up, Perbedaan dengan dokumen lama yang diselesaikan, Sesi dan keuangan (+1 more)

### Community 151 - "Konteks final proyek NgeBadmintonYuk"
Cohesion: 0.18
Nodes (11): Alur dan batas integrasi, Identitas dan scope, Konteks final proyek NgeBadmintonYuk, Konteks siap dipakai untuk pekerjaan berikutnya, Pembaruan antarmuka: indikator proses server, Pembaruan terbaru: notifikasi wajib bagi member, Peta sumber, Riwayat verifikasi audit awal, sebelum tahap kas–kuota (+3 more)

### Community 153 - "UserFactory"
Cohesion: 0.36
Nodes (3): static, UserFactory, Illuminate\Support\Facades\Hash

### Community 168 - "FinanceTest.php"
Cohesion: 0.22
Nodes (4): Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, static, TestCase

### Community 175 - "StoreStockMovementRequest"
Cohesion: 0.28
Nodes (3): RecordStockMovementAction, StockMovementController, StoreStockMovementRequest

### Community 178 - "bootstrap/app.php"
Cohesion: 0.40
Nodes (3): Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware

## Knowledge Gaps
- **707 isolated node(s):** `composer install`, `Illuminate\\Foundation\\ComposerScripts::postAutoloadDump`, `Illuminate\\Foundation\\ComposerScripts::prePackageUninstall`, `@lint:check`, `npm install` (+702 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **34 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `PlaySession`, `Illuminate\Database\Eloquent\Model`, `Illuminate\Foundation\Http\FormRequest`, `PushNotificationSender.php`, `Illuminate\Http\RedirectResponse`, `UserFactory`, `RecordAttendanceAction`, `Illuminate\Database\Seeder`, `FinanceTest.php`, `RegistrationController.php`, `StoreStockMovementRequest`, `Illuminate\Database\Eloquent\Relations\HasMany`, `Illuminate\Validation\ValidationException`, `PushNotification`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `PushSubscription`, `ReportService`, `Income`, `AppServiceProvider.php`, `Illuminate\View\View`, `web.php`, `ShuttlecockItem`, `SessionRegistration`?**
  _High betweenness centrality (0.056) - this node is a cross-community bridge._
- **Why does `Modul` connect `Modul` to `07-expense.md`?**
  _High betweenness centrality (0.022) - this node is a cross-community bridge._
- **Why does `PlaySession` connect `PlaySession` to `RecordAttendanceAction`, `Illuminate\Database\Eloquent\Model`, `Illuminate\Database\Seeder`, `ReportService`, `Illuminate\Foundation\Http\FormRequest`, `SessionRegistration`, `PushNotificationSender.php`, `Income`, `Illuminate\View\View`, `Illuminate\Database\Eloquent\Relations\HasMany`, `User`, `Illuminate\Http\RedirectResponse`, `Illuminate\Validation\ValidationException`, `PushNotification`, `ShuttlecockItem`, `PushSubscription`?**
  _High betweenness centrality (0.022) - this node is a cross-community bridge._
- **What connects `composer install`, `Illuminate\\Foundation\\ComposerScripts::postAutoloadDump`, `Illuminate\\Foundation\\ComposerScripts::prePackageUninstall` to the rest of the system?**
  _707 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `scripts` be split into smaller, more focused modules?**
  _Cohesion score 0.057057057057057055 - nodes in this community are weakly interconnected._
- **Should `PlaySession` be split into smaller, more focused modules?**
  _Cohesion score 0.1103448275862069 - nodes in this community are weakly interconnected._
- **Should `package.json` be split into smaller, more focused modules?**
  _Cohesion score 0.0625 - nodes in this community are weakly interconnected._