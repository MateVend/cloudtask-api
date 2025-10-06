# CloudTask Pro - Project Management Application

A modern, full-featured project management application built with React and Laravel. Manage projects, tasks, teams, and collaborate seamlessly with your organization.

![CloudTask Pro](https://img.shields.io/badge/version-1.0.0-blue.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)

## Features

### Core Features
- **Project Management** - Create, organize, and track unlimited projects
- **Task Management** - Kanban boards with drag-and-drop functionality
- **Team Collaboration** - Invite team members, assign roles, and manage permissions
- **Real-time Notifications** - Stay updated with WebSocket-powered notifications via Pusher
- **Global Search** - Quickly find projects, tasks, and team members
- **Advanced Analytics** - Track progress with beautiful charts and insights
- **Dark Mode** - System-wide theme switching with persistence
- **Responsive Design** - Fully optimized for mobile, tablet, and desktop

### Subscription Plans
- **Free** - 3 Projects, 5 Team Members, Basic Features
- **Pro** ($29/month) - 50 Projects, 20 Team Members, Priority Support
- **Enterprise** ($99/month) - Unlimited Projects & Members, Advanced Features

### Payment Integration
- Stripe Checkout for subscription management
- Automatic plan upgrades/downgrades
- Secure payment processing

## Tech Stack

### Frontend
- **React 18** - UI library
- **React Router v6** - Client-side routing
- **Tailwind CSS** - Utility-first styling
- **Lucide React** - Icon library
- **Pusher** - Real-time WebSocket communication
- **Axios** - HTTP client

### Backend
- **Laravel 10** - PHP framework
- **MySQL** - Database
- **Laravel Sanctum** - API authentication
- **Stripe API** - Payment processing
- **Pusher** - WebSocket server

## Project Structure

```
cloudtask-pro/
├── frontend/
│   ├── src/
│   │   ├── components/        # Reusable UI components
│   │   ├── context/          # React Context providers
│   │   │   ├── AppContext.jsx       # User & auth state
│   │   │   └── ThemeContext.jsx     # Theme management
│   │   ├── pages/            # Page components
│   │   │   ├── LandingPage.jsx
│   │   │   ├── Login.jsx
│   │   │   ├── Register.jsx
│   │   │   ├── Dashboard.jsx
│   │   │   ├── Projects.jsx
│   │   │   ├── ProjectDetail.jsx
│   │   │   ├── Tasks.jsx
│   │   │   ├── Team.jsx
│   │   │   └── Settings.jsx
│   │   ├── services/         # API services
│   │   │   ├── api.js
│   │   │   └── pusher.js
│   │   ├── utils/            # Utility functions
│   │   ├── App.jsx           # Root component
│   │   └── main.jsx          # Entry point
│   ├── public/
│   ├── tailwind.config.js
│   └── package.json
│
└── backend/
    ├── app/
    │   ├── Http/Controllers/
    │   │   ├── Auth/
    │   │   ├── ProjectController.php
    │   │   ├── TaskController.php
    │   │   ├── TeamController.php
    │   │   ├── OrganizationController.php
    │   │   ├── NotificationController.php
    │   │   └── PaymentController.php
    │   ├── Models/
    │   └── Services/
    ├── database/
    │   └── migrations/
    ├── routes/
    │   ├── api.php
    │   └── web.php
    └── config/
```

## Installation

### Prerequisites
- Node.js 18+ and npm
- PHP 8.1+
- Composer
- MySQL 8.0+
- Stripe Account
- Pusher Account

### Backend Setup

1. **Clone the repository**
```bash
git clone https://github.com/yourusername/cloudtask-pro.git
cd cloudtask-pro/backend
```

2. **Install dependencies**
```bash
composer install
```

3. **Environment configuration**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure .env file**
```env
APP_NAME="CloudTask Pro"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cloudtask
DB_USERNAME=root
DB_PASSWORD=

STRIPE_KEY=your_stripe_publishable_key
STRIPE_SECRET=your_stripe_secret_key
STRIPE_WEBHOOK_SECRET=your_webhook_secret

PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=your_cluster

BROADCAST_DRIVER=pusher
QUEUE_CONNECTION=database
```

5. **Run migrations**
```bash
php artisan migrate --seed
```

6. **Start the server**
```bash
php artisan serve
```

### Frontend Setup

1. **Navigate to frontend directory**
```bash
cd ../frontend
```

2. **Install dependencies**
```bash
npm install
```

3. **Configure environment**
```bash
cp .env.example .env
```

```env
VITE_API_URL=http://localhost:8000/api
VITE_PUSHER_KEY=your_pusher_key
VITE_PUSHER_CLUSTER=your_pusher_cluster
```

4. **Start development server**
```bash
npm run dev
```

The application will be available at `http://localhost:5173`

## Configuration

### Tailwind CSS Setup

Add to your `tailwind.config.js`:

```javascript
module.exports = {
  darkMode: 'class',
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
```

### CSS Configuration

Add to your `src/index.css`:

```css
@tailwind base;
@tailwind components;
@tailwind utilities;

:root {
  color-scheme: light dark;
}

.dark {
  color-scheme: dark;
}

* {
  transition-property: background-color, border-color, color;
  transition-duration: 200ms;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
```

## Usage

### Authentication

**Register a new account:**
```javascript
POST /api/register
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "organization_name": "Acme Inc"
}
```

**Login:**
```javascript
POST /api/login
{
  "email": "john@example.com",
  "password": "password123"
}
```

### Project Management

**Create a project:**
```javascript
POST /api/projects
{
  "name": "Website Redesign",
  "description": "Redesign company website",
  "color": "#8b5cf6",
  "status": "active"
}
```

**Get all projects:**
```javascript
GET /api/projects
```

### Task Management

**Create a task:**
```javascript
POST /api/tasks
{
  "project_id": 1,
  "title": "Design homepage",
  "description": "Create mockup for new homepage",
  "priority": "high",
  "status": "todo",
  "assigned_to": 2,
  "due_date": "2024-12-31"
}
```

**Update task status (drag and drop):**
```javascript
PUT /api/tasks/:id
{
  "status": "in_progress"
}
```

### Subscription Management

**Create Stripe checkout session:**
```javascript
POST /api/payments/checkout
{
  "plan": "pro"
}
```

**Cancel subscription:**
```javascript
POST /api/payments/cancel
```

## Dark Mode Implementation

The application supports system-wide dark mode:

1. **ThemeContext** manages theme state globally
2. **localStorage** persists user preference
3. All components use `dark:` Tailwind variants
4. Theme toggle available in navbar and settings

```javascript
// Using theme in components
import { useTheme } from '../context/ThemeContext'

const { theme, toggleTheme } = useTheme()
```

## Responsive Design

The application is fully responsive with breakpoints:

- **Mobile**: < 640px (Bottom navigation)
- **Tablet**: 640px - 1024px
- **Desktop**: > 1024px (Sidebar layout)

### Mobile Features
- Bottom navigation bar
- Slide-in drawer menu
- Touch-optimized interactions
- Responsive top bar

### Desktop Features
- Collapsible sidebar
- Full search functionality
- Extended navigation options

## Real-time Notifications

Notifications are powered by Pusher:

```javascript
// Subscribe to user channel
pusher.subscribe(`user.${userId}`)

// Listen for notifications
channel.bind('notification.new', (data) => {
  // Handle notification
})
```

## Deployment

### Frontend (Vercel/Netlify)

1. Build the application:
```bash
npm run build
```

2. Deploy the `dist` folder to your hosting provider

### Backend (Laravel Forge/DigitalOcean)

1. Set up server with PHP 8.1+, Nginx, MySQL
2. Configure domain and SSL
3. Set environment variables
4. Run migrations:
```bash
php artisan migrate --force
```
5. Configure queue worker:
```bash
php artisan queue:work
```

## Testing

### Frontend Tests
```bash
npm run test
```

### Backend Tests
```bash
php artisan test
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support, email support@cloudtaskpro.com or join our Slack channel.

## Acknowledgments

- React Team for the amazing framework
- Tailwind CSS for the utility-first CSS framework
- Laravel Team for the robust backend framework
- Stripe for payment processing
- Pusher for real-time functionality

## Roadmap

- [ ] Calendar view for tasks
- [ ] Time tracking
- [ ] File attachments
- [ ] Comments and mentions
- [ ] Email notifications
- [ ] Mobile apps (iOS/Android)
- [ ] Integrations (Slack, GitHub, etc.)
- [ ] AI-powered insights
- [ ] Advanced reporting
- [ ] API documentation

## Screenshots

### Dashboard
Beautiful overview of all your projects and tasks with real-time analytics.

### Kanban Board
Drag-and-drop task management with column-based workflow.

### Dark Mode
Seamless dark mode across the entire application.

### Mobile View
Fully responsive design optimized for mobile devices.

---

**Built with ❤️ by the CloudTask Pro Team**
