# Mobile API

All authenticated endpoints require a Sanctum bearer token:

```http
Authorization: Bearer <token>
Accept: application/json
```

## Auth

### Login

```http
POST /api/login
```

Body:

```json
{
  "email": "admin@example.com",
  "password": "password"
}
```

Returns:

```json
{
  "token": "...",
  "user": {}
}
```

### Current User

```http
GET /api/me
```

### Logout

```http
POST /api/logout
```

## Dashboard

```http
GET /api/dashboard
```

Returns:

- pickups due today
- returns due today
- overdue returns
- pickup, return, and overdue counts
- car status totals

Agent users only see their branch. Manager and super admin users can see all branches unless filters are added later.

## Cars

### List Cars

```http
GET /api/cars
```

Supported query parameters:

```text
search
status
pickup_datetime
return_datetime
```

Availability example:

```http
GET /api/cars?pickup_datetime=2026-07-10 10:00:00&return_datetime=2026-07-12 10:00:00
```

### Show Car

```http
GET /api/cars/{id}
```

### Update Status

```http
PUT /api/cars/{id}/status
```

Body:

```json
{
  "status": "maintenance",
  "notes": "Oil leak inspection"
}
```

Allowed statuses:

```text
available, rented, reserved, maintenance, out_of_service
```

The API blocks marking a car as available when it still has an active or overdue booking.

## Clients

### List Clients

```http
GET /api/clients
```

Supported query parameters:

```text
search
```

### Create Client

```http
POST /api/clients
```

Body:

```json
{
  "full_name": "Client Name",
  "phone": "+213555555555",
  "email": "client@example.com"
}
```

## Bookings

### List Bookings

```http
GET /api/bookings
```

Supported query parameters:

```text
date
```

### Create Booking

```http
POST /api/bookings
```

Body:

```json
{
  "client_id": 1,
  "car_id": 1,
  "pickup_datetime": "2026-07-10 10:00:00",
  "return_datetime_planned": "2026-07-12 10:00:00",
  "daily_rate_agreed": 8000,
  "deposit_amount": 10000,
  "discount": 0
}
```

The API rejects overlapping reserved, active, or overdue bookings.

### Check In

```http
POST /api/bookings/{id}/check-in
```

Body:

```json
{
  "pickup_mileage": 10000,
  "pickup_fuel_level": "Full"
}
```

Only reserved bookings can be checked in.

### Check Out

```http
POST /api/bookings/{id}/check-out
```

Body:

```json
{
  "return_mileage": 10300,
  "return_fuel_level": "3/4"
}
```

Only active or overdue bookings can be checked out.
