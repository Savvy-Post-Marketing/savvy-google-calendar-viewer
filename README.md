# Savvy Google Calendar Viewer

A WordPress plugin that displays Google Calendar events on your website using the FullCalendar library and a responsive, user-friendly modal interface.

## Description

Savvy Google Calendar Viewer integrates Google Calendar with your WordPress website, allowing you to display events in a beautiful, interactive calendar. It features a clean modal popup for event details and is fully responsive for both desktop and mobile devices.

## Features

- Display Google Calendar events in a monthly or list view
- Responsive design that adapts to all screen sizes
- Customizable event colors
- Modal popup with event details
- Accessible design with keyboard navigation
- Body scroll lock when viewing event details
- SEO-friendly implementation

## Installation

1. Upload the `savvy-google-calendar` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Savvy Google Calendar to configure your Google API key and Calendar ID

## Configuration

### Google API Setup

1. Go to the [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project
3. Enable the Google Calendar API
4. Create an API key with HTTP referrer restrictions for your domain
5. Copy the API key to use in the plugin settings

### Plugin Settings

1. Navigate to Settings > Savvy Google Calendar in your WordPress admin
2. Enter your Google API Key
3. Enter your Google Calendar ID (can be found in your Google Calendar settings)
4. Choose default view (Month or List Week)
5. Set maximum number of events to display
6. Select event color and text color
7. Save your settings

## Usage

Add the shortcode `[savvy_google_calendar]` to any page or post where you want to display the calendar.

Example:

```
[savvy_google_calendar]
```

## Styling

The plugin comes with default styling that should work well with most WordPress themes. If you need to customize the appearance, you can add custom CSS to your theme.

### CSS Classes

- `.savvy-modal` - The modal container
- `.savvy-modal-content` - The modal content box
- `.savvy-event-details` - The event details container
- `.event-meta` - Event metadata (date, location)
- `.event-description` - Event description

## Troubleshooting

### Calendar Not Displaying

- Ensure your Google API key is correct and has Calendar API enabled
- Verify the Calendar ID is correct
- Make sure the calendar is set to public or has appropriate sharing settings
- Check browser console for JavaScript errors

### Events Not Showing

- Confirm that the calendar has events in the visible date range
- Check that the events are set to public visibility
- Ensure your API key has not reached its quota limits

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher
- A Google account with Calendar API access

## Changelog

### 1.0
- Initial release

## Credits

- Built with [FullCalendar](https://fullcalendar.io/) 5.11.3
- Developed by Savvy Post Marketing

## License

This plugin is licensed under the GPL v2 or later.

```
Savvy Google Calendar Viewer
Copyright (C) 2023 Savvy Post Marketing

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## Support

For support, please contact Savvy Post Marketing or submit an issue on GitHub.