# আহসান ইলেকট্রনিক শপ (Ahsan Electronic Shop)

PHP + MySQL ক্যাটালগ ওয়েবসাইট - WhatsApp অর্ডার সিস্টেম সহ।

## ইনস্টলেশন (XAMPP)

### ধাপ ১: প্রজেক্ট কপি করুন
এই ফোল্ডারটি `/opt/lampp/htdocs/ahsanelectronic_shop` এ রাখুন।

### ধাপ ২: XAMPP চালু করুন
Apache এবং MySQL সার্ভিস চালু করুন।

### ধাপ ৩: ডাটাবেস সেটআপ

**পদ্ধতি ১ (সহজ):** ব্রাউজারে যান:
```
http://localhost/ahsanelectronic_shop/install.php
```
এটি স্বয়ংক্রিয়ভাবে ডাটাবেস ও টেবিল তৈরি করবে।

**পদ্ধতি ২ (ম্যানুয়াল):**
1. phpMyAdmin এ যান: `http://localhost/phpmyadmin`
2. `database.sql` ফাইলটি ইম্পোর্ট করুন

### ধাপ ৪: ওয়েবসাইট দেখুন
```
http://localhost/ahsanelectronic_shop/
```

## অ্যাডমিন প্যানেল

```
http://localhost/ahsanelectronic_shop/admin/login.php
```

**ডিফল্ট লগইন:**
- ইউজারনেম: `admin`
- পাসওয়ার্ড: `admin123`

> গুরুত্বপূর্ণ: প্রোডাকশনে অবশ্যই পাসওয়ার্ড পরিবর্তন করুন!

## ফিচার সমূহ

- ক্যাটাগরি ভিত্তিক পণ্য ক্যাটালগ
- WhatsApp এ সরাসরি অর্ডার
- অ্যাডমিন প্যানেল (পণ্য, ক্যাটাগরি, সেটিংস)
- ছবি আপলোড সিস্টেম
- Tailwind CSS দিয়ে সুন্দর ডিজাইন
- বাংলা ভাষায় সম্পূর্ণ

## প্রজেক্ট স্ট্রাকচার

```
ahsanelectronic_shop/
├── index.php          # হোমপেজ
├── product.php        # পণ্যের বিস্তারিত
├── category.php       # ক্যাটাগরি পেজ
├── db.php             # ডাটাবেস সংযোগ
├── install.php        # ইনস্টলেশন স্ক্রিপ্ট
├── database.sql       # SQL ফাইল
├── admin/
│   ├── login.php      # অ্যাডমিন লগইন
│   ├── dashboard.php  # ড্যাশবোর্ড
│   ├── add-product.php  # পণ্য যোগ/সম্পাদনা
│   ├── add-category.php # ক্যাটাগরি যোগ/সম্পাদনা
│   └── settings.php   # সাইট সেটিংস
├── uploads/           # পণ্যের ছবি
└── css/               # CSS ফাইল
```

## WhatsApp নম্বর

ডিফল্ট WhatsApp নম্বর: `+8801768870308`

অ্যাডমিন প্যানেল > সেটিংস থেকে পরিবর্তন করা যাবে।
