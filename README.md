# Leyte Tourism Guide Web System

A web-based tourism guide system designed to help users explore selected tourist destinations in Leyte.

The system is developed using Scrum, with features implemented progressively through separate Scrum/Jira branches.

## Project Status

### Sprint 1: User Identity & Profile
- User Registration
- User Login
- User Logout
- Manage User Profile
- Save User Preferences
- Home Dashboard

### Sprint 2: Destination Discovery & Administration
- Destination Directory
- Destination Search and Filtering
- Destination Details
- Admin Privileges
- Admin Destination Management (CRUD)
- Business Destination Requests
- Pin Location Map Picker for Admin Destinations and Business Requests

### Sprint 3: Route & Weather Planning
- Route Planning
- Weather Information
- Weather Advisories
- Day-Trip Planning

### Sprint 4: User Engagement
- Ratings and Reviews
- Favorites
- Completed-Trip History Log
- Review Eligibility Based on Completed Trips

Features may be implemented progressively, and their availability depends on the current project branch.

## Planned Sprints

### Sprint 1: User Identity & Profile
- User registration and authentication
- User logout
- Profile management
- User preferences
- Home dashboard

### Sprint 2: Destination Discovery & Administration
- Destination directory
- Destination search and filtering
- Destination details
- Admin privileges and access control
- Admin destination management (Create, Read, Update, Delete)
- Business destination request submission
- Admin review and approval of destination requests
- Interactive Pin Location map picker for admin-created and edited destinations
- Interactive Pin Location map picker for business destination requests
- Admin location verification and correction before approval

### Sprint 3: Route & Weather Planning
- Route planning
- Weather information using the OpenWeather API
- Weather advisories
- Day-trip planning

### Sprint 4: User Engagement
- Destination ratings and reviews
- Favorites
- Completed-trip history logs
- Review submission for eligible completed trips
- Display of destination ratings, reviews, and average ratings

## Technologies

- HTML
- CSS
- JavaScript
- PHP
- Bootstrap 5
- File-based data storage
- OpenWeather API
- Composer dependencies
- Leaflet and OpenStreetMap for interactive maps, if used in the current implementation

## Pin Location Feature

The system provides an interactive map picker for destination locations.

- Admins can pin locations when adding or editing destinations.
- Business owners can pin their business locations when submitting destination requests.
- Admins can view and correct submitted pins before approving requests.
- Latitude and longitude are stored internally for map display and navigation.
- The forms display a Pin Location map picker instead of separate latitude and longitude input fields.

## Project Structure

```text
WEB-LEYTE-TOURISM/
├── api/              # Backend API endpoints
├── css/              # Stylesheets
├── data/             # Local application data
├── images/            # Images and icons
├── includes/          # Shared PHP configuration and utilities
├── js/                # JavaScript files
├── vendor/             # Project dependencies
├── dashboard.html      # Home dashboard
├── login.html           # Login page
├── register.html        # Registration page
├── router.php           # Local PHP router
├── .gitignore
└── README.md
```

The actual project structure may contain additional files for administration, destination requests, trip planning, history logs, and reviews.

## Data Storage and Architecture

- Uses PHP for backend processing and API endpoints.
- Uses file-based storage for application data.
- Uses PHP sessions for authentication.
- Preserves the existing project architecture without requiring a migration to MySQL or Supabase.

## Development Approach

The project follows the Scrum methodology. Features are organized into sprints and implemented through separate Scrum/Jira branches to support incremental development, testing, and integration.

