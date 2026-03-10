<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channel Authorization
|--------------------------------------------------------------------------
|
| conversation.{id}     — Public channel, accessible by both widget and admin.
|                         No auth needed since visitor uses token-based access.
|
| admin.site.{siteId}   — Public channel for admin notifications.
|                         Will be converted to private in production.
|
*/

// Placeholder for future private channel authorization
// Broadcast::channel('admin.site.{siteId}', function ($user, $siteId) {
//     return $user->sites()->where('id', $siteId)->exists();
// });
