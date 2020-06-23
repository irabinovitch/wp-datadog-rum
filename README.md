# Datadog RUM for Wordpress
  
Instrument Wordpress sites with [Datadog Real User Monitoring](https://docs.datadoghq.com/real_user_monitoring/installation/) (RUM).
You will need to create a Datadog [Client Token](https://docs.datadoghq.com/account_management/api-app-keys/#client-tokens) and RUM ApplicationId by creating a [new RUM app](https://app.datadoghq.com/rum/create).

##  Installation

1. Upload `datadog-rum.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Add Datdog Client Token and RUM ApplicationId to settings page.
