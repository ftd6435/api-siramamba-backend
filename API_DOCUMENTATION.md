# SIRAMAMBA API Documentation

## Table of Contents

1. [Base URL & Conventions](#1-base-url--conventions)
2. [Standard Response Format](#2-standard-response-format)
3. [Authentication](#3-authentication)
4. [Public Endpoints (Website)](#4-public-endpoints-website)
5. [Admin Endpoints (Dashboard)](#5-admin-endpoints-dashboard)
6. [Angular Frontend Integration Guide](#6-angular-frontend-integration-guide)

---

## 1. Base URL & Conventions

- **Base URL**: `http://your-domain.com/api`
- **API Version**: `v1`
- **All endpoints are prefixed with**: `/api/v1/`
- **Content-Type**: `application/json` (except file uploads: `multipart/form-data`)
- **Authentication**: Bearer Token via Laravel Sanctum
- **Date format**: `d-m-Y H:i:s` for timestamps, `Y-m-d` for dates
- **Image uploads**: Max 2048KB (2MB), formats: PNG, JPG, JPEG
- **Language**: Error messages are in French (fr)

### Required Headers

```json
{
    "Accept": "application/json",
    "Content-Type": "application/json"
}
```

### Authenticated Headers

```json
{
    "Accept": "application/json",
    "Content-Type": "application/json",
    "Authorization": "Bearer {sanctum_token}"
}
```

---

## 2. Standard Response Format

### Success Response (status: 1)

```json
{
    "status": 1,
    "message": "Opération réussie.",
    "data": {
        /* resource or array of resources */
    }
}
```

### Success With Token (Login/Register)

```json
{
    "status": 1,
    "message": "Utilisateur connecté avec succès.",
    "data": {
        /* UserResource */
    },
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
}
```

### Success - No Content (Delete operations)

```json
{
    "status": 1,
    "message": "Supprimé avec succès."
}
```

### Error Response (status: 0)

```json
{
    "status": 0,
    "message": "Message d'erreur principal.",
    "error": {
        "field_name": ["Message de validation 1", "Message de validation 2"]
    }
}
```

### HTTP Status Codes

| Code | Meaning                                  |
| ---- | ---------------------------------------- |
| 200  | OK - Success                             |
| 201  | Created - Resource created               |
| 401  | Unauthorized - Missing/invalid token     |
| 403  | Forbidden - Insufficient permissions     |
| 404  | Not Found - Resource doesn't exist       |
| 422  | Unprocessable Entity - Validation errors |
| 500  | Server Error                             |

---

## 3. Authentication

All auth routes: `/api/v1/auth/*`

### 3.1 Register

**POST** `/api/v1/auth/register` — **Public**

Creates a new user account and returns a token.

**Request Payload** (`multipart/form-data`):

```typescript
// Angular FormData
const form = new FormData();
form.append("name", "Jean Dupont"); // required, string, 2-160 chars
form.append("telephone", "624000001"); // required, string, 9-14 chars, unique
form.append("email", "jean@example.com"); // nullable, email, unique
form.append("role", "user"); // nullable, in: user|super_admin|admin|client (default: user)
form.append("avatar", file); // nullable, image (PNG/JPG/JPEG), max 2MB
form.append("password", "password123"); // required, string, min 6 chars
form.append("password_confirmation", "password123"); // required, must match password
```

**Validation Rules**:
| Field | Type | Rules |
|-------|------|-------|
| name | string | required, min:2, max:160 |
| telephone | string | required, min:9, max:14, unique:users |
| email | string | nullable, email, unique:users |
| role | string | nullable, in: user, super_admin, admin, client |
| avatar | file | nullable, image, mimes:png,jpg,jpeg, max:2048 |
| password | string | required, min:6, confirmed |

**Success Response** (201):

```json
{
    "status": 1,
    "message": "Utilisateur créé avec succès.",
    "data": {
        "id": 1,
        "name": "Jean Dupont",
        "username": "jean1234",
        "telephone": "624000001",
        "email": "jean@example.com",
        "avatar_url": "https://cdn.example.com/avatars/abc123.jpg",
        "role": "user",
        "created_at": "11-08-2026 10:30:00",
        "updated_at": "11-08-2026 10:30:00"
    },
    "token": "1|AbCdEfGhIjKlMnOpQrStUvWxYz0123456789"
}
```

**Store the token** in Angular (localStorage / AuthService).

---

### 3.2 Login

**POST** `/api/v1/auth/login` — **Public**

Authenticate user with telephone + password.

**Request Body** (JSON):

```json
{
    "telephone": "624000001",
    "password": "password123"
}
```

**Validation Rules**:
| Field | Type | Rules |
|-------|------|-------|
| telephone | string | required, min:9 |
| password | string | required, min:6 |

**Success Response** (200):

```json
{
    "status": 1,
    "message": "Utilisateur connecté avec succès.",
    "data": {
        "id": 1,
        "name": "Jean Dupont",
        "username": "jean1234",
        "telephone": "624000001",
        "email": "jean@example.com",
        "avatar_url": "https://cdn.example.com/avatars/abc123.jpg",
        "role": "admin",
        "created_at": "11-08-2026 10:30:00",
        "updated_at": "11-08-2026 10:30:00"
    },
    "token": "2|AbCdEfGhIjKlMnOpQrStUvWxYz9876543210"
}
```

**Error Response** (404 — Invalid credentials):

```json
{
    "status": 0,
    "message": "Information invalide",
    "error": []
}
```

**Error Response** (403 — Account disabled):

```json
{
    "status": 0,
    "message": "Votre compte est désactivé",
    "error": []
}
```

---

### 3.3 Logout

**POST** `/api/v1/auth/logout` — **Requires Auth**

Invalidates all user tokens.

**Success Response** (200):

```json
{
    "status": 1,
    "message": "Utilisateur deconnecté avec succès."
}
```

---

### 3.4 Get Current User

**GET** `/api/v1/auth/me` — **Requires Auth**

Fetches currently authenticated user profile.

**Success Response** (200):

```json
{
    "status": 1,
    "message": "Utilisateur récupéré avec succès.",
    "data": {
        "id": 1,
        "name": "Jean Dupont",
        "username": "jean1234",
        "telephone": "624000001",
        "email": "jean@example.com",
        "avatar_url": "https://cdn.example.com/avatars/abc123.jpg",
        "role": "admin",
        "created_at": "11-08-2026 10:30:00",
        "updated_at": "11-08-2026 10:30:00"
    }
}
```

---

### 3.5 Update Profile

**PUT** `/api/v1/auth/me` — **Requires Auth**

Update current user's profile information. Use `multipart/form-data` if uploading avatar.

**Request Payload** (`multipart/form-data`):

```typescript
const form = new FormData();
form.append("name", "Jean Dupont Modifié"); // sometimes, string, 2-160
form.append("telephone", "624000099"); // sometimes, string, 9-14, unique (ignore self)
form.append("email", "jean.new@example.com"); // sometimes, email, unique (ignore self)
form.append("avatar", newFile); // nullable, image, max 2MB
```

**Validation Rules** (all fields are `sometimes`):
| Field | Type | Rules |
|-------|------|-------|
| name | string | sometimes, string, min:2, max:160 |
| telephone | string | sometimes, min:9, max:14, unique (except self) |
| email | string | sometimes, email, unique (except self) |
| avatar | file | nullable, image, mimes:png,jpg,jpeg, max:2048 |

**Success Response** (200): Same format as `me` with updated data.

---

### 3.6 Update Password

**PUT** `/api/v1/auth/password` — **Requires Auth**

Change current user's password.

**Request Body** (JSON):

```json
{
    "current_password": "oldpassword",
    "new_password": "newpassword123",
    "new_password_confirmation": "newpassword123"
}
```

**Validation Rules**:
| Field | Type | Rules |
|-------|------|-------|
| current_password | string | required |
| new_password | string | required, confirmed |

**Error Response** (4xx — wrong current password):

```json
{
    "status": 0,
    "message": "Mot de passe actuel incorrect",
    "error": []
}
```

---

### 3.7 List All Users (Admin)

**GET** `/api/v1/admin/users` — **Requires Auth**

Returns all users ordered by creation date.

---

### 3.8 Switch User Status (Super Admin Only)

**PATCH** `/api/v1/admin/switch-status/{user_id}` — **Requires Auth (super_admin)**

Toggle user active/inactive status. Only super_admin role can perform this action.

**Error Response** (403):

```json
{
    "status": 0,
    "message": "Vous n'avez pas la permission de changer le statut d'un utilisateur.",
    "error": []
}
```

---

## 4. Public Endpoints (Website)

These endpoints are **public** — no authentication required.
They only return `is_active: true` resources.

---

### 4.1 Services

#### List Active Services

**GET** `/api/v1/services`

Returns services sorted by `sort_order`, then `id`. Only active.

**Success Response** (200):

```json
{
    "status": 1,
    "message": "Services récupérés avec succès.",
    "data": [
        {
            "id": 1,
            "title": "Conseil minier",
            "short_description": "Description courte du service",
            "description": "<p>Description complète en HTML</p>",
            "sort_order": 1,
            "thumbnail_url": "https://cdn.example.com/services/thumbnails/thumb1.jpg",
            "images": [
                {
                    "id": 1,
                    "url": "https://cdn.example.com/services/images/img1.jpg",
                    "created_at": "01-01-2026 00:00:00",
                    "updated_at": "01-01-2026 00:00:00"
                }
            ],
            "created_at": "01-01-2026 00:00:00",
            "updated_at": "01-01-2026 00:00:00"
        }
    ]
}
```

#### Get Single Service

**GET** `/api/v1/services/{service_id}`

Returns 404 if service is not active.

---

### 4.2 Categories (for Projects & Blogs)

#### List Active Categories

**GET** `/api/v1/categories`

Returns categories (types: `mix`, `projet`, `blog`).

**Response Sample**:

```json
{
    "status": 1,
    "message": "Categories récupérés avec succès.",
    "data": [
        {
            "id": 1,
            "name": "Infrastructure",
            "description": "Description catégorie",
            "type": "projet",
            "is_active": true,
            "created_at": "01-01-2026 00:00:00",
            "updated_at": "01-01-2026 00:00:00"
        }
    ]
}
```

---

### 4.3 Projects

#### List Active Projects

**GET** `/api/v1/projects`

Returns only `is_active: true` projects with category + images loaded.

**Response Sample**:

```json
{
    "status": 1,
    "message": "Projects récupérés avec succès.",
    "data": [
        {
            "id": 1,
            "category": {
                "id": 1,
                "name": "Infrastructure",
                "description": "Description",
                "type": "projet",
                "is_active": true,
                "created_at": "01-01-2026 00:00:00",
                "updated_at": "01-01-2026 00:00:00"
            },
            "title": "Construction du pont X",
            "short_description": "Résumé court",
            "description": "<p>Description complète</p>",
            "status": "encours",
            "is_featured": true,
            "country": "Guinée",
            "city": "Conakry",
            "address": "Boulevard du Commerce",
            "start_date": "2026-01-15",
            "end_date": "2026-12-31",
            "progess_percentage": 65,
            "list_details": ["Travaux de fondation", "Structure métallique"],
            "is_active": true,
            "thumbnail_url": "https://cdn.example.com/projects/thumbnails/proj1.jpg",
            "images": [
                {
                    "id": 1,
                    "url": "https://cdn.example.com/projects/images/img1.jpg",
                    "created_at": "01-01-2026 00:00:00",
                    "updated_at": "01-01-2026 00:00:00"
                }
            ],
            "created_at": "01-01-2026 00:00:00",
            "updated_at": "01-01-2026 00:00:00"
        }
    ]
}
```

**Project Status Values**: `encours` | `terminer` | `planifier`

#### Get Single Project

**GET** `/api/v1/projects/{project_id}`

404 if not active. Includes category + images.

#### List Project Comments

**GET** `/api/v1/projects/{project_id}/comments`

Returns all comments for a project with nested replies.

```json
{
    "status": 1,
    "data": [
        {
            "id": 1,
            "name": "Visiteur",
            "email": "visiteur@example.com",
            "content": "Excellent projet !",
            "parent_id": null,
            "replies": [
                {
                    "id": 2,
                    "name": "Réponse",
                    "email": null,
                    "content": "Merci !",
                    "parent_id": 1,
                    "replies": [],
                    "created_at": "01-01-2026 00:00:00",
                    "updated_at": "01-01-2026 00:00:00"
                }
            ],
            "created_at": "01-01-2026 00:00:00",
            "updated_at": "01-01-2026 00:00:00"
        }
    ]
}
```

#### Add Project Comment

**POST** `/api/v1/projects/{project_id}/comments` — **Public**

**Request Body**:

```json
{
    "name": "Visiteur Anonyme", // required, string, max:255
    "email": "visiteur@example.com", // nullable, email, max:255
    "content": "Commentaire ici.", // required, string
    "parent_id": 1 // nullable, integer, must exist in blog_comments for this project
}
```

---

### 4.4 Blogs

#### List Active Blogs

**GET** `/api/v1/blogs`

Returns only active blogs, latest first, with category + images loaded.

**Response Sample**:

```json
{
    "status": 1,
    "message": "Blogs récupérés avec succès.",
    "data": [
        {
            "id": 1,
            "category_id": 2,
            "category": {
                "id": 2,
                "name": "Actualités",
                "description": "Catégorie blog",
                "type": "blog",
                "is_active": true,
                "created_at": "01-01-2026 00:00:00",
                "updated_at": "01-01-2026 00:00:00"
            },
            "title": "Titre de l'article",
            "short_description": "Extrait court",
            "description": "<p>Contenu complet de l'article</p>",
            "thumbnail_url": "https://cdn.example.com/blogs/thumbnails/blog1.jpg",
            "is_featured": true,
            "is_active": true,
            "images": [
                {
                    "id": 1,
                    "url": "https://cdn.example.com/blogs/images/blogimg1.jpg",
                    "created_at": "01-01-2026 00:00:00",
                    "updated_at": "01-01-2026 00:00:00"
                }
            ],
            "created_at": "01-01-2026 00:00:00",
            "updated_at": "01-01-2026 00:00:00"
        }
    ]
}
```

#### Get Single Blog

**GET** `/api/v1/blogs/{blog_id}` — 404 if not active.

#### List Blog Comments

**GET** `/api/v1/blogs/{blog_id}/comments`

Same structure as project comments.

#### Add Blog Comment

**POST** `/api/v1/blogs/{blog_id}/comments` — **Public**

Same payload structure as project comments.

---

### 4.5 Gallery Categories

#### List Active Gallery Categories

**GET** `/api/v1/gallery-categories`

```json
{
    "status": 1,
    "data": [
        {
            "id": 1,
            "name": "Photos 2026",
            "created_at": "01-01-2026 00:00:00",
            "updated_at": "01-01-2026 00:00:00"
        }
    ]
}
```

---

### 4.6 Galleries

#### List Active Galleries

**GET** `/api/v1/galleries`

Only active galleries returned. Includes category info.

```json
{
    "status": 1,
    "data": [
        {
            "id": 1,
            "category_id": 1,
            "category": {
                "id": 1,
                "name": "Photos 2026"
            },
            "image_url": "https://cdn.example.com/galleries/gal1.jpg",
            "short_description": "Légende photo",
            "is_active": true,
            "created_at": "01-01-2026 00:00:00",
            "updated_at": "01-01-2026 00:00:00"
        }
    ]
}
```

#### Get Single Gallery

**GET** `/api/v1/galleries/{gallery_id}` — 404 if not active.

---

### 4.7 Team Members

#### List Active Team

**GET** `/api/v1/teams`

Only active members. Uses `PublicTeamResource` (no is_active or audit info).

```json
{
    "status": 1,
    "data": [
        {
            "id": 1,
            "name": "Dr. Alpha Bah",
            "post": "Directeur Général",
            "short_description": "Biographie courte",
            "avatar_url": "https://cdn.example.com/teams/avatar1.jpg",
            "created_at": "01-01-2026 00:00:00",
            "updated_at": "01-01-2026 00:00:00"
        }
    ]
}
```

#### Get Single Team Member

**GET** `/api/v1/teams/{team_id}` — 404 if not active.

---

### 4.8 Testimonials

#### List All Testimonials

**GET** `/api/v1/testimonials`

All testimonials are public (no `is_active` filter on public endpoint).

```json
{
    "status": 1,
    "data": [
        {
            "id": 1,
            "name": "Client Satisfait",
            "profession": "Entrepreneur",
            "message": "Excellent travail ! Je recommande.",
            "created_at": "01-01-2026 00:00:00",
            "updated_at": "01-01-2026 00:00:00"
        }
    ]
}
```

---

### 4.9 Newsletter Subscription

#### Subscribe to Newsletter

**POST** `/api/v1/newsletters` — **Public**

**Request Body**:

```json
{
    "name": "Nom Optionnel", // nullable, string, max:255
    "email": "email@example.com" // required, email, max:255
}
```

---

### 4.10 Event Categories

#### List Event Categories

**GET** `/api/v1/event-categories`

```json
{
    "status": 1,
    "data": [
        {
            "id": 1,
            "name": "Conférences",
            "is_active": true,
            "created_at": "01-01-2026 00:00:00",
            "updated_at": "01-01-2026 00:00:00"
        }
    ]
}
```

#### Get Single Event Category

**GET** `/api/v1/event-categories/{eventCategory_id}`

---

### 4.11 Events

#### List Events

**GET** `/api/v1/events`

Returns all events with category + images.

```json
{
    "status": 1,
    "data": [
        {
            "id": 1,
            "category": {
                "id": 1,
                "name": "Conférences",
                "is_active": true,
                "created_at": "01-01-2026 00:00:00",
                "updated_at": "01-01-2026 00:00:00"
            },
            "title": "Séminaire Minier 2026",
            "short_description": "Événement annuel",
            "description": "<p>Description complète</p>",
            "status": "planifier",
            "is_featured": true,
            "country": "Guinée",
            "city": "Kankan",
            "address": "Palais des Congrès",
            "start_date": "15-09-2026",
            "end_date": "18-09-2026",
            "list_details": ["Inscription", "Cocktail d'ouverture"],
            "is_active": true,
            "thumbnail_url": "https://cdn.example.com/events/event1_thumb.jpg",
            "video_url_link": "https://youtube.com/watch?v=example",
            "images": [
                {
                    "id": 1,
                    "image_url": "https://cdn.example.com/events/event1_img1.jpg",
                    "created_at": "01-01-2026 00:00:00"
                }
            ],
            "created_at": "01-01-2026 00:00:00",
            "updated_at": "01-01-2026 00:00:00"
        }
    ]
}
```

#### Get Single Event

**GET** `/api/v1/events/{event_id}`

---

### 4.12 Event Testimonials

#### List Event Testimonials

**GET** `/api/v1/event-testimonials`

```json
{
    "status": 1,
    "data": [
        {
            "id": 1,
            "name": "Participant",
            "message": "Événement très bien organisé !",
            "created_at": "01-01-2026 00:00:00"
        }
    ]
}
```

---

### 4.13 Partner Types

#### List Partner Types

**GET** `/api/v1/type-partners`

```json
{
    "status": 1,
    "data": [
        {
            "id": 1,
            "name": "Partenaire Financier",
            "is_active": true,
            "created_at": "01-01-2026 00:00:00",
            "updated_at": "01-01-2026 00:00:00"
        }
    ]
}
```

#### Get Single Partner Type

**GET** `/api/v1/type-partners/{typePartner_id}`

---

### 4.14 Partners

#### List Partners

**GET** `/api/v1/partners`

```json
{
    "status": 1,
    "data": [
        {
            "id": 1,
            "type_partner": {
                "id": 1,
                "name": "Partenaire Financier",
                "is_active": true,
                "created_at": "01-01-2026 00:00:00",
                "updated_at": "01-01-2026 00:00:00"
            },
            "company": "Banque Mondiale",
            "short_description": "Description partenaire",
            "logo_url": "https://cdn.example.com/partners/logo1.png",
            "website_link": "https://banque-mondiale.example.org",
            "is_active": true,
            "created_at": "01-01-2026 00:00:00",
            "updated_at": "01-01-2026 00:00:00"
        }
    ]
}
```

#### Get Single Partner

**GET** `/api/v1/partners/{partner_id}`

---

### 4.15 Contact (Formulaire de contact)

#### Submit Contact Form

**POST** `/api/v1/contacts` — **Public**

Submits a contact message. The system will automatically:

1.  Persist the message in the database
2.  **Queue** an admin notification email to `MAIL_FROM_ADDRESS` (with all contact details + Reply-To set to visitor)
3.  **Queue** an auto-reply confirmation email back to the visitor (with dossier number + message recap + branding)

Both emails are sent asynchronously via the Laravel Queue (database driver by default).

**Request Body** (JSON):

```json
{
    "name": "Abdoulaye Diallo",
    "email": "a.diallo@example.com",
    "telephone": "624000001",
    "subject": "Demande de partenariat minier",
    "message": "Bonjour, je souhaiterais entrer en contact avec votre service commercial pour discuter d'un éventuel partenariat dans l'exploitation d'or dans la région de Siguiri."
}
```

**Validation Rules**:
| Field | Type | Rules |
|-------|------|-------|
| name | string | required |
| email | string | required, email |
| telephone | string | required, max:30 |
| subject | string | required |
| message | string | required |

**Success Response** (200/201 — contact is saved, emails are queued):

```json
{
    "status": 1,
    "message": "Message envoyer avec succès",
    "data": {
        "id": 1,
        "name": "Abdoulaye Diallo",
        "email": "a.diallo@example.com",
        "telephone": "624000001",
        "subject": "Demande de partenariat minier",
        "message": "Bonjour, je souhaiterais entrer en contact avec votre service commercial pour discuter d'un éventuel partenariat dans l'exploitation d'or dans la région de Siguiri.",
        "created_at": "11-08-2026 14:35:12",
        "updated_at": "11-08-2026 14:35:12"
    }
}
```

**Validation Error Response** (422):

```json
{
    "status": 0,
    "message": "Le nom est requis (et 3 autres erreurs)",
    "error": {
        "name": ["Le nom est requis"],
        "email": ["L'email est requis", "L'email doit être au format valide"],
        "telephone": ["Le téléphone est requis"],
        "subject": ["Le sujet est requis"],
        "message": ["Le message est requis"]
    }
}
```

---

## 5. Admin Endpoints (Dashboard)

All admin routes require **Authentication** via Bearer token.
Admin routes are prefixed with `/api/v1/admin/*` (except Events, EventCategories, Participants, Partners, TypePartners which use `/api/v1/*` but still require auth for write operations).

Admin resources include **audit fields** (`created_by`, `updated_by`) and `is_active` flag.

---

### 5.1 Services CRUD (Admin)

#### List All Services (including inactive)

**GET** `/api/v1/admin/services`

Uses `ServiceResource` — includes `is_active`, `created_by`, `updated_by`.

```json
{
    "status": 1,
    "data": [
        {
            "id": 1,
            "title": "Conseil minier",
            "short_description": "...",
            "description": "...",
            "sort_order": 1,
            "thumbnail_url": "...",
            "is_active": true,
            "images": [
                /* ... */
            ],
            "created_by": {
                "id": 1,
                "name": "Admin User",
                "username": "admin1234",
                "telephone": "624000000",
                "email": "admin@example.com",
                "avatar_url": "...",
                "role": "admin",
                "created_at": "...",
                "updated_at": "..."
            },
            "updated_by": {
                /* UserResource */
            },
            "created_at": "...",
            "updated_at": "..."
        }
    ]
}
```

#### Create Service

**POST** `/api/v1/admin/services` — **Requires Auth**

**Request** (`multipart/form-data`):

```typescript
const form = new FormData();
form.append("title", "Nouveau Service"); // required, string, max:255
form.append("short_description", "Résumé"); // required, string
form.append("description", "<p>Full desc</p>"); // required, string
form.append("sort_order", "1"); // required, integer
form.append("thumbnail", file); // required, image, max 2MB
form.append("is_active", "true"); // required, boolean
// Option A: Attach new uploaded images
form.append("images[0]", file1); // array of images, max 2MB each
form.append("images[1]", file2);
// Option B: Attach pre-uploaded orphan images by ID
form.append("image_ids[0]", "5"); // integer array, orphan service_images
form.append("image_ids[1]", "6");
```

**Validation Rules**:
| Field | Type | Rules |
|-------|------|-------|
| title | string | required, max:255 |
| short_description | string | required |
| description | string | required |
| sort_order | integer | required |
| thumbnail | file | required, image, mimes:png,jpg,jpeg, max:2048 |
| is_active | boolean | required |
| images | array | sometimes, array of image files |
| image_ids | array | sometimes, distinct integers, existing service_images with service_id = NULL |

**Response** (201): Full `ServiceResource` with creator/updater info.

#### Get Single Service (Admin)

**GET** `/api/v1/admin/services/{service_id}`

#### Update Service

**PATCH** `/api/v1/admin/services/{service_id}` — **Requires Auth**

Same fields as Create, all `sometimes`. Use `multipart/form-data`.

#### Delete Service

**DELETE** `/api/v1/admin/services/{service_id}` — **Requires Auth**

Removes service + all associated images from storage.

#### Upload Service Editor Image (Pre-upload)

**POST** `/api/v1/admin/services/images` — **Requires Auth**

Uploads an image that is initially orphan (no service_id). Use this for CKEditor / rich text editor image uploads. Then attach using `image_ids` during create/update.

**Request**:

```typescript
const form = new FormData();
form.append("image", file); // required, image, max 2MB
```

#### Delete Service Image

**DELETE** `/api/v1/admin/services/{service_id}/images/{serviceImage_id}` — **Requires Auth**

---

### 5.2 Categories CRUD (Admin)

#### List All Categories

**GET** `/api/v1/admin/categories`

#### Create Category

**POST** `/api/v1/admin/categories` — **Requires Auth**

**Request Body**:

```json
{
    "name": "Nouvelle Catégorie", // required, string, max:255
    "description": "Description", // required, string
    "type": "projet", // required, in: mix|projet|blog
    "is_active": true // required, boolean
}
```

**Category Types**:

- `mix` — usable by both projects and blogs
- `projet` — only for projects
- `blog` — only for blogs

#### Get / Update (PATCH) / Delete

Standard CRUD: `GET`, `PATCH`, `DELETE` at `/api/v1/admin/categories/{category_id}`

---

### 5.3 Projects CRUD (Admin)

#### List All Projects

**GET** `/api/v1/admin/projects`

#### Create Project

**POST** `/api/v1/admin/projects` — **Requires Auth**

**Request** (`multipart/form-data`):

```typescript
const form = new FormData();
form.append("category_id", "1"); // required, integer, exists:categories
form.append("title", "Titre Projet"); // required, string, max:255
form.append("short_description", "Résumé"); // required, string
form.append("description", "<p>Description</p>"); // required, string
form.append("status", "encours"); // required, in: encours|terminer|planifier
form.append("is_featured", "true"); // required, boolean
form.append("country", "Guinée"); // nullable, string, max:255
form.append("city", "Conakry"); // nullable, string, max:255
form.append("address", "Adresse"); // required, string, max:255
form.append("start_date", "2026-01-15"); // required, date
form.append("end_date", "2026-12-31"); // nullable, date, after_or_equal:start_date
form.append("progess_percentage", "65"); // required, integer, 0-100
// list_details: send as JSON string or repeated keys
form.append("list_details[0]", "Tâche 1");
form.append("list_details[1]", "Tâche 2");
form.append("is_active", "true"); // required, boolean
form.append("thumbnail", file); // required, image, max 2MB
```

#### Get / Update (PATCH) / Delete

Standard at `/api/v1/admin/projects/{project_id}`.

#### Project Images CRUD

**List images**: `GET /api/v1/admin/projects/{project_id}/images`
**Add image**: `POST /api/v1/admin/projects/{project_id}/images` (form-data, `image` field required)
**Delete image**: `DELETE /api/v1/admin/projects/{project_id}/images/{projectImage_id}`

---

### 5.4 Blogs CRUD (Admin)

#### List All Blogs

**GET** `/api/v1/admin/blogs`

#### Create Blog

**POST** `/api/v1/admin/blogs` — **Requires Auth**

**Request** (`multipart/form-data`):

```typescript
const form = new FormData();
form.append("category_id", "2"); // required, integer, exists categories type blog|mix
form.append("title", "Titre Article"); // required, string, max:255
form.append("short_description", "Résumé"); // required, string
form.append("description", "<p>Full article</p>"); // required, string
form.append("thumbnail", file); // required, image, max 2MB
form.append("is_featured", "true"); // required, boolean
form.append("is_active", "true"); // required, boolean
// Option A: Attach pre-uploaded blog images
form.append("image_ids[0]", "1"); // nullable, integer array, blog_images with blog_id = NULL
// Option B: Upload new images directly
form.append("images[0]", imageFile1); // nullable, array of images
form.append("images[1]", imageFile2);
```

#### Get / Update (PATCH) / Delete

Standard at `/api/v1/admin/blogs/{blog_id}`.

#### Blog Images — Pre-upload (Unattached)

**Upload unattached image**: `POST /api/v1/admin/blogs/images` (form-data: `image` field)
Returns the image ID to use in `image_ids` later.

**Delete unattached image**: `DELETE /api/v1/admin/blogs/images/{blogImage_id}`

#### Blog Images — Attached to Blog

**List**: `GET /api/v1/admin/blogs/{blog_id}/images`
**Attach new image to blog**: `POST /api/v1/admin/blogs/{blog_id}/images` (form-data: `image`)
**Delete attached**: `DELETE /api/v1/admin/blogs/{blog_id}/images/{blogImage_id}`

---

### 5.5 Gallery Categories CRUD (Admin)

Standard CRUD at `/api/v1/admin/gallery-categories`.

**Create/Update Payload**:

```json
{
    "name": "Nom Catégorie" // required, string, max:255 (sometimes for update)
}
```

---

### 5.6 Galleries CRUD (Admin)

Standard CRUD at `/api/v1/admin/galleries`.

**Create/Update Request** (`multipart/form-data`):

```typescript
const form = new FormData();
form.append("category_id", "1"); // required, integer, exists:category_galleries
form.append("image", file); // required, image, max 2MB
form.append("short_description", "Légende"); // required, string
form.append("is_active", "true"); // required, boolean
```

---

### 5.7 Team CRUD (Admin)

Standard CRUD at `/api/v1/admin/teams`.

**Create/Update Request** (`multipart/form-data`):

```typescript
const form = new FormData();
form.append("name", "Dr. Alpha Bah"); // required, string, max:255
form.append("post", "Directeur Général"); // required, string, max:255
form.append("short_description", "Bio"); // required, string
form.append("avatar", file); // required, image, max 2MB
form.append("is_active", "true"); // required, boolean
```

Uses `TeamResource` for admin (includes `is_active`, `created_by`, `updated_by`).

---

### 5.8 Testimonials CRUD (Admin)

Standard CRUD at `/api/v1/admin/testimonials`.

**Create/Update Payload**:

```json
{
    "name": "Nom Client", // required, string, max:255
    "profession": "Profession", // required, string, max:255
    "message": "Le témoignage..." // required, string
}
```

---

### 5.9 Newsletter CRUD (Admin)

**List all**: `GET /api/v1/admin/newsletters`
**Get single**: `GET /api/v1/admin/newsletters/{newsletter_id}`
**Update status**: `PATCH /api/v1/admin/newsletters/{newsletter_id}`
**Delete**: `DELETE /api/v1/admin/newsletters/{newsletter_id}`

**Update Payload**:

```json
{
    "status": "verifier" // sometimes, in: attente|verifier
}
```

**Newsletter Status Values**: `attente` | `verifier`

---

### 5.10 Settings CRUD (Admin)

Standard CRUD at `/api/v1/admin/settings`. Used for dynamic site settings (site name, logo, social links, etc.).

**Create Request** (`multipart/form-data` if type=image):

```typescript
const form = new FormData();
form.append("key", "site_name"); // required, string, max:255
form.append("type", "text"); // required, in: text|json|boolean|image
form.append("value", "SIRAMAMBA S.A."); // rules depend on type:
// text: required, string
// json: required, string, valid JSON
// boolean: required, boolean
// image: required, image file (PNG/JPG/JPEG ≤ 2MB)
```

**Setting Types**: `text`, `json`, `boolean`, `image`

**Response Sample** (SettingResource):

```json
{
    "id": 1,
    "key": "site_name",
    "value": "SIRAMAMBA S.A.", // type=json auto-parsed to object; image→URL; boolean→true/false
    "type": "text",
    "created_at": "...",
    "updated_at": "..."
}
```

---

### 5.11 Event Categories CRUD

List/Show: public. Create/Update/Delete: **Requires Auth**.

**POST** `/api/v1/event-categories`
**PUT** `/api/v1/event-categories/{id}`
**DELETE** `/api/v1/event-categories/{id}`

Payload:

```json
{
    "name": "Ateliers", // required, string, 2-160 (sometimes for update)
    "is_active": true // required, boolean (sometimes for update)
}
```

---

### 5.12 Events CRUD

List/Show: public. Create/Update/Delete: **Requires Auth**.

#### Create Event

**POST** `/api/v1/events` — **Requires Auth**

**Request** (`multipart/form-data`):

```typescript
const form = new FormData();
form.append("category_id", "1"); // required, integer, exists:event_categories
form.append("title", "Grand Séminaire"); // required, string, 2-200
form.append("short_description", "Résumé"); // required, string
form.append("description", "<p>Full desc</p>"); // required, string
form.append("status", "planifier"); // required, in: encours|terminer|planifier
form.append("is_featured", "true"); // required, boolean
form.append("country", "Guinée"); // nullable, string, max:100
form.append("city", "Conakry"); // nullable, string, max:100
form.append("address", "Adresse"); // required, string, max:255
form.append("start_date", "2026-09-15"); // required, date
form.append("end_date", "2026-09-18"); // nullable, date, after_or_equal:start_date
// list_details as array
form.append("list_details[0]", "Point 1");
form.append("list_details[1]", "Point 2");
form.append("is_active", "true"); // required, boolean
form.append("thumbnail", thumbnailFile); // required, image, max 2MB
form.append("video_url_link", "https://youtu.be/abc123"); // required, valid URL
// images array
form.append("images[0]", imageFile1); // nullable, array of images, max 2MB each
form.append("images[1]", imageFile2);
```

#### Update Event

**PUT** `/api/v1/events/{event_id}` — **Requires Auth**

Same fields all `sometimes`.

#### Delete Event

**DELETE** `/api/v1/events/{event_id}` — **Requires Auth**

#### Delete Event Image

**DELETE** `/api/v1/events/images/{image_id}` — **Requires Auth**

#### Upload Description Image (CKEditor)

**POST** `/api/v1/events/description-image` — **Requires Auth**

Used by rich text editor (CKEditor SimpleUploadAdapter). Uploads orphan image, auto-linked to event via description content parsing on save.

**Request**:

```typescript
const form = new FormData();
form.append("upload", file); // required, image, max 2MB (field name is "upload")
```

**Response**:

```json
{
    "url": "https://cdn.example.com/events/desc_image1.jpg"
}
```

Note: This endpoint returns **raw JSON** (not wrapped in the standard status/data envelope) to be CKEditor-compatible.

---

### 5.13 Participants CRUD

All endpoints **Require Auth**.

**List**: `GET /api/v1/participants`
**Get single**: `GET /api/v1/participants/{participant_id}`
**Create**: `POST /api/v1/participants`
**Update**: `PUT /api/v1/participants/{participant_id}`
**Delete**: `DELETE /api/v1/participants/{participant_id}`

**Create/Update Payload**:

```json
{
    "event_id": 1, // required, integer, exists:events
    "name": "Participant Nom", // required, string, 2-160
    "telephone": "624000001", // required, string, 9-14
    "address": "Adresse", // required, string, max:255
    "is_active": true // required, boolean
}
```

**ParticipantResource Response**:

```json
{
    "id": 1,
    "event": {
        /* EventResource, optional */
    },
    "name": "Participant Nom",
    "telephone": "624000001",
    "address": "Adresse",
    "is_active": true,
    "created_at": "...",
    "updated_at": "..."
}
```

---

### 5.14 Event Testimonials CRUD

List: public. Create/Update/Delete: **Requires Auth**.

**Create**: `POST /api/v1/event-testimonials`
**Update**: `PUT /api/v1/event-testimonials/{id}`
**Delete**: `DELETE /api/v1/event-testimonials/{id}`

Payload:

```json
{
    "name": "Nom", // required, string, 2-160 (sometimes for update)
    "message": "Msg" // required, string (sometimes for update)
}
```

---

### 5.15 Type Partners CRUD

List/Show: public. Create/Update/Delete: **Requires Auth**.

**POST** `/api/v1/type-partners`
**PUT** `/api/v1/type-partners/{id}`
**DELETE** `/api/v1/type-partners/{id}`

Payload:

```json
{
    "name": "Bailleurs de fonds", // required, string, 2-160
    "is_active": true // required, boolean
}
```

---

### 5.16 Partners CRUD

List/Show: public. Create/Update/Delete: **Requires Auth**.

#### Create Partner

**POST** `/api/v1/partners` — **Requires Auth**

**Request** (`multipart/form-data`):

```typescript
const form = new FormData();
form.append("type_partner_id", "1"); // required, integer, exists:type_partners
form.append("company", "Nom Entreprise"); // required, string, 2-160
form.append("short_description", "Description"); // required, string
form.append("logo", logoFile); // required, image, max 2MB
form.append("website_link", "https://example.com"); // required, valid URL
form.append("is_active", "true"); // required, boolean
```

**Update**: `PUT /api/v1/partners/{id}` — same fields, all `sometimes`.

---

### 5.17 Contacts CRUD (Admin)

Create is public. List + Delete require **Authentication** (Sanctum).

#### List All Contact Messages (Admin)

**GET** `/api/v1/contacts` — **Requires Auth**

Returns all contact submissions sorted by newest first.

**Success Response**:

```json
{
    "status": 1,
    "message": "Liste des contacts",
    "data": [
        {
            "id": 1,
            "name": "Abdoulaye Diallo",
            "email": "a.diallo@example.com",
            "telephone": "624000001",
            "subject": "Demande de partenariat",
            "message": "Bonjour, ...",
            "created_at": "11-08-2026 14:35:12",
            "updated_at": "11-08-2026 14:35:12"
        }
    ]
}
```

#### Delete Contact Message

**DELETE** `/api/v1/contacts/{contact_id}` — **Requires Auth**

Uses implicit route model binding — returns 404 if contact doesn't exist.

**Success Response**:

```json
{
    "status": 1,
    "message": "Contact supprimé avec succès"
}
```

---

## 6. Angular Frontend Integration Guide

### 6.1 Recommended User Flow (Angular App)

```
Visitor (Public Site)
├── Home page: /services, /testimonials, /settings (for site info)
├── About page: /teams, /partners, /type-partners
├── Services page: /services → /services/:id
├── Projects page: /categories (filter) → /projects → /projects/:id → /projects/:id/comments (POST comment)
├── Blog page: /categories (filter) → /blogs → /blogs/:id → /blogs/:id/comments (POST comment)
├── Gallery page: /gallery-categories → /galleries → /galleries/:id
├── Events page: /event-categories → /events → /events/:id
├── Event testimonials: /event-testimonials
├── Newsletter subscribe: POST /newsletters (footer form)
├── Contact page: POST /contacts (formulaire → envoi email asynchrone + accusé réception)
└── Auth:
    ├── Login: POST /auth/login → store token → redirect to dashboard
    └── Register: POST /auth/register → store token

Authenticated Admin (Dashboard)
├── Auth management: /auth/me, /auth/logout, /auth/me (PUT), /auth/password (PUT)
├── Service CRUD, Service images upload
├── Category CRUD (mix/projet/blog)
├── Project CRUD, Project images, Project comments admin
├── Blog CRUD, Blog images (pre-upload + attach), Blog comments admin
├── Gallery CRUD + Gallery Categories
├── Team CRUD
├── Testimonial CRUD
├── Newsletter list + update status
├── Settings CRUD (dynamic site config)
├── Event Category CRUD
├── Event CRUD + images + description image upload (CKEditor)
├── Participant CRUD
├── Event Testimonial CRUD
├── Partner Type CRUD
├── Partner CRUD
└── Contact messages: /contacts (list) + /contacts/:id (delete)
└── Super Admin only:
    ├── /admin/users (list)
    └── /admin/switch-status/:id (toggle user active)
```

### 6.2 Angular Auth Service Example

```typescript
// auth.service.ts
import { Injectable } from "@angular/core";
import { HttpClient, HttpHeaders } from "@angular/common/http";
import { BehaviorSubject, Observable, tap } from "rxjs";

const API_URL = "http://localhost:8000/api/v1";

interface AuthResponse {
    status: number;
    message: string;
    data: any;
    token?: string;
}

@Injectable({ providedIn: "root" })
export class AuthService {
    private tokenSubject$ = new BehaviorSubject<string | null>(
        localStorage.getItem("token"),
    );
    private userSubject$ = new BehaviorSubject<any>(null);

    constructor(private http: HttpClient) {}

    get token$(): Observable<string | null> {
        return this.tokenSubject$.asObservable();
    }

    get token(): string | null {
        return this.tokenSubject$.value;
    }

    getHeaders(): HttpHeaders {
        const headers = new HttpHeaders({ Accept: "application/json" });
        if (this.token) {
            return headers.set("Authorization", `Bearer ${this.token}`);
        }
        return headers;
    }

    login(telephone: string, password: string): Observable<AuthResponse> {
        return this.http
            .post<AuthResponse>(`${API_URL}/auth/login`, {
                telephone,
                password,
            })
            .pipe(tap((res) => this.handleAuthSuccess(res)));
    }

    register(formData: FormData): Observable<AuthResponse> {
        return this.http
            .post<AuthResponse>(`${API_URL}/auth/register`, formData)
            .pipe(tap((res) => this.handleAuthSuccess(res)));
    }

    private handleAuthSuccess(res: AuthResponse): void {
        if (res.status === 1 && res.token) {
            localStorage.setItem("token", res.token);
            this.tokenSubject$.next(res.token);
            this.userSubject$.next(res.data);
        }
    }

    me(): Observable<AuthResponse> {
        return this.http.get<AuthResponse>(`${API_URL}/auth/me`, {
            headers: this.getHeaders(),
        });
    }

    updateProfile(formData: FormData): Observable<AuthResponse> {
        return this.http.put<AuthResponse>(`${API_URL}/auth/me`, formData, {
            headers: this.getHeaders(),
        });
    }

    updatePassword(
        current_password: string,
        new_password: string,
        new_password_confirmation: string,
    ): Observable<AuthResponse> {
        return this.http.put<AuthResponse>(
            `${API_URL}/auth/password`,
            { current_password, new_password, new_password_confirmation },
            { headers: this.getHeaders() },
        );
    }

    logout(): Observable<AuthResponse> {
        return this.http
            .post<AuthResponse>(
                `${API_URL}/auth/logout`,
                {},
                {
                    headers: this.getHeaders(),
                },
            )
            .pipe(
                tap(() => {
                    localStorage.removeItem("token");
                    this.tokenSubject$.next(null);
                    this.userSubject$.next(null);
                }),
            );
    }

    isAuthenticated(): boolean {
        return !!this.token;
    }
}
```

### 6.3 Angular HttpInterceptor

```typescript
// auth.interceptor.ts
import { Injectable } from "@angular/core";
import {
    HttpRequest,
    HttpHandler,
    HttpEvent,
    HttpInterceptor,
} from "@angular/common/http";
import { Observable } from "rxjs";
import { AuthService } from "./auth.service";

@Injectable()
export class AuthInterceptor implements HttpInterceptor {
    constructor(private auth: AuthService) {}

    intercept(
        request: HttpRequest<unknown>,
        next: HttpHandler,
    ): Observable<HttpEvent<unknown>> {
        const token = this.auth.token;
        if (token) {
            request = request.clone({
                setHeaders: {
                    Authorization: `Bearer ${token}`,
                    Accept: "application/json",
                },
            });
        } else {
            request = request.clone({
                setHeaders: { Accept: "application/json" },
            });
        }
        return next.handle(request);
    }
}
```

### 6.4 File Upload Helper (Angular)

For endpoints requiring `multipart/form-data`:

```typescript
// generic service example
createBlog(payload: {
  category_id: number;
  title: string;
  short_description: string;
  description: string;
  thumbnail: File;
  is_featured: boolean;
  is_active: boolean;
  image_ids?: number[];
  images?: File[];
}): Observable<any> {
  const form = new FormData();
  form.append('category_id', String(payload.category_id));
  form.append('title', payload.title);
  form.append('short_description', payload.short_description);
  form.append('description', payload.description);
  form.append('thumbnail', payload.thumbnail);
  form.append('is_featured', String(payload.is_featured));
  form.append('is_active', String(payload.is_active));

  (payload.image_ids || []).forEach((id, i) => {
    form.append(`image_ids[${i}]`, String(id));
  });

  (payload.images || []).forEach((file, i) => {
    form.append(`images[${i}]`, file);
  });

  return this.http.post(`${API_URL}/admin/blogs`, form);
}
```

### 6.5 Handling Validation Errors (Angular)

```typescript
// component.ts
submit() {
  this.auth.login(this.form.value).subscribe({
    next: (res) => {
      if (res.status === 1) {
        this.router.navigate(['/dashboard']);
      }
    },
    error: (err) => {
      if (err.status === 422 && err.error?.error) {
        // Fill Angular reactive form errors
        Object.keys(err.error.error).forEach((field) => {
          const messages = err.error.error[field];
          this.form.get(field)?.setErrors({ server: messages[0] });
        });
        this.globalError = err.error.message;
      } else if (err.error?.message) {
        this.globalError = err.error.message; // e.g. "Information invalide"
      }
    },
  });
}
```

### 6.6 Enum Constants to Copy

```typescript
// enums.ts
export const CATEGORY_TYPES = ["mix", "projet", "blog"] as const;
export const PROJECT_STATUSES = ["encours", "terminer", "planifier"] as const;
export const EVENT_STATUSES = ["encours", "terminer", "planifier"] as const;
export const USER_ROLES = ["user", "super_admin", "admin", "client"] as const;
export const NEWSLETTER_STATUSES = ["attente", "verifier"] as const;
export const SETTING_TYPES = ["text", "json", "boolean", "image"] as const;
```

### 6.7 Quick Endpoints Summary Table

| #       | Method | Path                                    | Auth              | Purpose                           |
| ------- | ------ | --------------------------------------- | ----------------- | --------------------------------- |
| 1       | POST   | `/api/v1/auth/register`                 | No                | Register user                     |
| 2       | POST   | `/api/v1/auth/login`                    | No                | Login user                        |
| 3       | POST   | `/api/v1/auth/logout`                   | Yes               | Logout                            |
| 4       | GET    | `/api/v1/auth/me`                       | Yes               | Get profile                       |
| 5       | PUT    | `/api/v1/auth/me`                       | Yes               | Update profile                    |
| 6       | PUT    | `/api/v1/auth/password`                 | Yes               | Update password                   |
| 7       | GET    | `/api/v1/admin/users`                   | Yes               | List users                        |
| 8       | PATCH  | `/api/v1/admin/switch-status/{id}`      | Yes (super_admin) | Toggle user status                |
| --      | --     | **Public Content**                      | --                | --                                |
| 9       | GET    | `/api/v1/services`                      | No                | List active services              |
| 10      | GET    | `/api/v1/services/{id}`                 | No                | View service                      |
| 11      | GET    | `/api/v1/categories`                    | No                | List active categories            |
| 12      | GET    | `/api/v1/projects`                      | No                | List active projects              |
| 13      | GET    | `/api/v1/projects/{id}`                 | No                | View project                      |
| 14      | GET    | `/api/v1/projects/{id}/comments`        | No                | List project comments             |
| 15      | POST   | `/api/v1/projects/{id}/comments`        | No                | Add project comment               |
| 16      | GET    | `/api/v1/blogs`                         | No                | List active blogs                 |
| 17      | GET    | `/api/v1/blogs/{id}`                    | No                | View blog                         |
| 18      | GET    | `/api/v1/blogs/{id}/comments`           | No                | List blog comments                |
| 19      | POST   | `/api/v1/blogs/{id}/comments`           | No                | Add blog comment                  |
| 20      | GET    | `/api/v1/gallery-categories`            | No                | List gallery categories           |
| 21      | GET    | `/api/v1/galleries`                     | No                | List active galleries             |
| 22      | GET    | `/api/v1/galleries/{id}`                | No                | View gallery                      |
| 23      | GET    | `/api/v1/teams`                         | No                | List active team                  |
| 24      | GET    | `/api/v1/teams/{id}`                    | No                | View team member                  |
| 25      | GET    | `/api/v1/testimonials`                  | No                | List testimonials                 |
| 26      | POST   | `/api/v1/newsletters`                   | No                | Subscribe newsletter              |
| 27      | GET    | `/api/v1/event-categories`              | No                | List event categories             |
| 28      | GET    | `/api/v1/events`                        | No                | List events                       |
| 29      | GET    | `/api/v1/events/{id}`                   | No                | View event                        |
| 30      | GET    | `/api/v1/event-testimonials`            | No                | List event testimonials           |
| 31      | GET    | `/api/v1/type-partners`                 | No                | List partner types                |
| 32      | GET    | `/api/v1/partners`                      | No                | List partners                     |
| --      | --     | **Admin CRUD**                          | --                | --                                |
| 33-37   | CRUD   | `/api/v1/admin/services`                | Yes               | Services admin                    |
| 38      | POST   | `/api/v1/admin/services/images`         | Yes               | Pre-upload service editor image   |
| 39      | DELETE | `/api/v1/admin/services/{s}/images/{i}` | Yes               | Delete service image              |
| 40-44   | CRUD   | `/api/v1/admin/categories`              | Yes               | Categories admin                  |
| 45-49   | CRUD   | `/api/v1/admin/projects`                | Yes               | Projects admin                    |
| 50-52   | Images | `/api/v1/admin/projects/{id}/images`    | Yes               | Project images                    |
| 53-57   | CRUD   | `/api/v1/admin/blogs`                   | Yes               | Blogs admin                       |
| 58      | POST   | `/api/v1/admin/blogs/images`            | Yes               | Pre-upload blog image             |
| 59      | DELETE | `/api/v1/admin/blogs/images/{id}`       | Yes               | Delete unattached blog image      |
| 60-62   | Images | `/api/v1/admin/blogs/{id}/images`       | Yes               | Attached blog images              |
| 63-67   | CRUD   | `/api/v1/admin/gallery-categories`      | Yes               | Gallery categories                |
| 68-72   | CRUD   | `/api/v1/admin/galleries`               | Yes               | Galleries                         |
| 73-77   | CRUD   | `/api/v1/admin/teams`                   | Yes               | Team members                      |
| 78-82   | CRUD   | `/api/v1/admin/testimonials`            | Yes               | Testimonials                      |
| 83-86   | CRUD   | `/api/v1/admin/newsletters`             | Yes               | Newsletter subscribers            |
| 87-91   | CRUD   | `/api/v1/admin/settings`                | Yes               | Site settings                     |
| 92-96   | CRUD   | `/api/v1/event-categories`              | Yes (write)       | Event categories                  |
| 97-101  | CRUD   | `/api/v1/events`                        | Yes (write)       | Events                            |
| 102     | DELETE | `/api/v1/events/images/{id}`            | Yes               | Delete event image                |
| 103     | POST   | `/api/v1/events/description-image`      | Yes               | Upload CKEditor event image       |
| 104-108 | CRUD   | `/api/v1/participants`                  | Yes               | Event participants                |
| 109-111 | CRUD   | `/api/v1/event-testimonials`            | Yes (write)       | Event testimonials                |
| 112-116 | CRUD   | `/api/v1/type-partners`                 | Yes (write)       | Partner types                     |
| 117-121 | CRUD   | `/api/v1/partners`                      | Yes (write)       | Partners                          |
| 122     | POST   | `/api/v1/contacts`                      | No                | Submit public contact form        |
| 123     | GET    | `/api/v1/contacts`                      | Yes               | List all contact messages (admin) |
| 124     | DELETE | `/api/v1/contacts/{id}`                 | Yes               | Delete contact message (admin)    |

---

**End of Documentation**
