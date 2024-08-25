# Drupal Plausible Pageviews Module

The **Plausible Pageviews Drupal 10 module** integrates Plausible Analytics with your Drupal site, allowing you to store pageviews data for your content. It does this by adding a custom *Plausible pageviews* integer field type that captures pageviews based on the path of each node and a user-defined time period.

This functionality enables site editors to build sorting and filtering based on real-world popularity data.

## Features

- **Pageviews Tracking:** Automatically fetches and updates pageviews with data from Plausible Analytics.
- **Automatic Updates:** Updates pageviews data using Drupal’s CRON system according to your site’s schedule.

## Installation and Configuration

Follow these steps to set up the Plausible Pageviews module:

1. **Install the Module:**
    - Copy the `plausible_pageviews` folder into `/web/modules/custom`.
2. **Enable the Module:**
    - Install the module via the Drupal admin interface.
3. **Configure the Module:**
    - Go to `/admin/config/system/plausible-pageviews` in your Drupal admin menu.
    - Enter your Plausible API token in the "Bearer token" field. (You can obtain this token from your Plausible account.)
    - Select your desired time period based on Plausible’s period format.
    - Set your Plausible site ID.
4. **Add the Plausible Pageviews Field to Content Types:**
    - Navigate to the content types where you want to track pageviews.
    - Add the `plausible_pageviews` field to each content type (using default parameters).
5. **Run CRON:**
    - Ensure Drupal’s CRON job is scheduled to run regularly to keep the pageviews data up-to-date.

## Limitations

- **Rate Limiting and Pagination:** This module does not handle Plausible’s rate limiting (600 requests per hour) or pagination (1000 results per page).
- **Multilingual Sites:** The module currently sums pageviews for nodes with the same ID regardless of the field's translatability. For example, `/contact-us` and `/fr/me-contacter` will be matched to the same node ID, and their pageviews will be summed together.

## Disclaimer

This module is provided “as is” without any warranty. It serves as a foundation that you can build upon and customize based on your project’s specific needs.
