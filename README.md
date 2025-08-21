<<<<<<< HEAD
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://c.top4top.io/p_3520svq3v1.png"  alt="Laravel Logo"></a></p>

# Tender Management System

A dynamic and real-time Tender Management System built with Laravel and Livewire. This application provides a streamlined solution for managing company tenders, featuring a clean user interface, powerful filtering, and data export capabilities.

---

## ✨ Key Features

-   **Modular Design:** Separate, fully functional modules for "E-Tenders" and "Other Tenders".
-   **Livewire-Powered Interface:** A fast, single-page experience for all user interactions.
-   **Full CRUD Functionality:** Create, Read, Update, and Delete tenders through an intuitive modal interface without page reloads.
-   **Dynamic Nested Forms:** Easily add and remove multiple "Focal Points" (contact persons) for each tender, with a validation limit of 5.
-   **Real-Time Filtering & Search:** Instantly search and filter tenders by Quarter, Status, Assignee, or Client.
-   **Data Export:** Download the filtered data as a **PDF** or **Excel** file with a single click.
-   **Read-Only View Mode:** Securely view tender details in a disabled form to prevent accidental edits.

---

## 🛠️ Tech Stack

-   **Backend:** Laravel
-   **Frontend:** Livewire (with Blade & Bootstrap 5)
-   **Database:** MySQL
-   **PDF Generation:** `barryvdh/laravel-dompdf`

---

## 👥 Project Supervision

This project was developed under the guidance of the following supervisors:

| Name  | Role                  |
| :---- | :-------------------- |
| Dua   | Frontend & UI/UX      |
| Anwar | Backend & Architecture|

---

## 🚀 Installation

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/your-username/your-repo-name.git
    cd your-repo-name
    ```

2.  **Install dependencies:**
    ```bash
    composer install
    ```

3.  **Setup environment:**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4.  **Configure your database** in the `.env` file.

5.  **Run database migrations:**
    ```bash
    php artisan migrate
    ```

6.  **Start the server:**
    ```bash
    php artisan serve
    ```
