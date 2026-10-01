# omnibus/royal-mail

Royal Mail for [glitchr/omnibus](https://github.com/glitchr-studio/omnibus): orders with their
postage and labels through Click & Drop, tracking through the Tracking API. Prices come from
configuration (`rates`): Click & Drop quotes none.

```yaml
omnibus:
    gateways:
        royal-mail:
            factory: royal-mail
            options:
                api_key: '%env(ROYAL_MAIL_API_KEY)%'            # Click & Drop > Settings > Integrations > API
                tracking_client_id: '%env(RM_TRACKING_ID)%'     # optional: the Tracking API
                tracking_client_secret: '%env(RM_TRACKING_SECRET)%'
                rates: [...]
```

The service is a Click & Drop service code (TPN01 Tracked 24, TPS01 Tracked 48, TRN01 Tracked 24
with signature...; none: the account's default). Shipment option: `package_format` (parcel,
largeLetter, letter...). No pickup points: Royal Mail's are its post offices.

Credentials: a Click & Drop business account's API key, and - for tracking - an app at
[developer.royalmail.net](https://developer.royalmail.net) with the Tracking API v2.

Built from Royal Mail's published API documentation and tested on recorded answers; not yet run
against the service: that needs the credentials above.

License: LGPL-3.0-or-later.
