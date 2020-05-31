=== Quantcast Choice ===
Version: 1.2.2
Contributors: rbaronqc
Tags: GDPR, GDPR Consent, Quantcast, Quantcast Choice, QC Choice
Requires at least: 4.0
Tested up to: 4.9.4
Stable tag: 1.2.2

The Quantcast Choice plugin implements the [Quantcast Choice GDPR Consent Tool - Consumer Demo](https://quantcast.mgr.consensu.org/index.html).

== Description ==

The Quantcast Choice plugin implements the [Quantcast Choice GDPR Consent Tool - Consumer Demo](https://quantcast.mgr.consensu.org/index.html).

IAB Europe announced a technical standard to support the digital advertising ecosystem in meeting the General Data Protection Regulation (GDPR) consumer consent requirements. This provides an implementation of that framework.

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload `quantcast-choice` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Navigate to the Quantcast Choice admin page and configure your settings.

== Frequently Asked Questions ==

= What is GDPR? =

The EU General Data Protection Regulation (GDPR) is a comprehensive privacy regulation that will replace the current Data Protection Directive 95/46/EC, with an implementation date of May 25, 2018. In April 2016, after more than four years of negotiation, the European Union approved the GDPR, with the goals of strengthening and harmonizing data protection regulation for individuals across the EU and strengthening the digital economy in the EU. The GDPR is directly applicable to member states without the need for implementing national legislation.

For more information and resources about GDPR, visit the [IAB Europe’s GDPR informational website](https://www.iabeurope.eu/?s=gdpr).

= When does the GDPR come in effect? =

The GDPR will be enforceable beginning on May 25, 2018.

= To whom does the GDPR apply? =

The GDPR applies to any business, whether or not it is based in the EU, that processes the personal data of EU citizens. The GDPR applies to these businesses even if the goods or services that they offer are free.

= How does Quantcast Choice, or a Consent Management Provider (CMP) solution work? =

Quantcast, or any other IAB approved Consent Management Provider (CMP), provides publishers and advertisers with a mechanism to obtain consent, and then control which third-party vendors can request consent to track users of their websites and apps. [Read this white paper outlining the function of a CMP and the Quantcast Consent implementation](https://www.quantcast.com/resources/how-to-obtain-consumer-consent-under-gdpr/).

== Screenshots ==

1. Quantcast Choice - what a consumer would see.
2. Quantcast Choice - purpose level consent screen.
3. Quantcast Choice - vendor level consent screen.

== Changelog ==

= 1.0.0 =
* Initial Plugin Release

= 1.1.0 =
* Updated CMP JavaScript

= 1.2.0 =
* Fixing Vendor List, allowing admins to choose which Vendors to include.
* Fixing "Display UI" default value.  Displays for EU visitors only by default.
* Adding option for 2 initial screen custom links.
* Allowing HTML in Vendor and Purpose screen body text.

= 1.2.1 =
* Adding Non-Consent Display Frequency option.

= 1.2.2 =
* Adding Google Personalization option.
