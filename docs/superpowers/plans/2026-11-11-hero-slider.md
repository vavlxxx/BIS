# Editable hero slider implementation plan

Approved scope: each screen has its own image, title, description and button label/link. Extend existing Hero admin menu without plugins. Retain legacy image-only configuration and existing homepage when new slides are absent.

- [x] Add slide normalization, defaults and secure admin persistence with capability and nonce checks.
- [x] Extend admin cards with content fields, media selection and sortable order.
- [x] Render slide content, controls and responsive styling in existing theme modules.
- [x] Implement accessible navigation, swipe, autoplay and pause on interaction/hidden document/reduced motion.
- [x] Verify normalization, PHP/JS syntax and existing tests; review diff; push to repository.

Security installation is a separate server task. Automatic approval rejected the initial live rollout; no server writes occurred. Prepare staging validation and full rollback before seeking explicit approval for revised rollout.
