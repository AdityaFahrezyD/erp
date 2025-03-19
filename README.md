<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
</p>

## 🚀 About Project

Texio Enterprise Resource Planning (ERP) is a comprehensive system designed to streamline Texio's financial management processes. This ERP project includes transaction history data, invoice management, and payroll management, enabling Texio's management to efficiently monitor and control the company's financial flow. By integrating these key financial components, the system enhances accuracy, reduces manual workload, and provides real-time insights, ensuring better decision-making and overall operational efficiency.

## 🛠️ How to Run the Project

###  1. Clone the Repository

```bash
git clone https://gitlab.com/texio/erp-texio.git
cd erp-texio
```

### 2. Install Dependencies

```bash
composer install
```
```bash
npm install && npm run dev
```

### 3. Setup Environment

```bash
cp .env.example .env
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Generate Application Key

```bash
php artisan migrate --seed
```

### 6. Run the Development Server

```bash
php artisan serve
```

## ⚒️ How to Run Create New Page

###  1. Setting up the database and models

```bash
php artisan make:model Test -m
```

###  2. Defining migrations

```php
// create_tests_table
Schema::create('tests', function (Blueprint $table) {
    $table->id();
    $table->string('test');
    $table->timestamps();
});
```

###  3. Setting up relationships between modelss

```php
class Test extends Model
{

}
```

###  4. Create Filament Resource (New Page)

```bash
php artisan make:filament-resource Test
```

###  5. Setting up the resource form

For more information you can see [Filement Documentation](https://filamentphp.com/docs/3.x/panels/getting-started#setting-up-the-resource-form)
