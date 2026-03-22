---
title: What to Expect After 6.0
description: Future roadmap and plans for UserFrosting beyond version 6.0
---

UserFrosting 6.0 represents a major milestone in the framework's evolution, but it's not the end of the journey. This page outlines what you can expect in terms of updates, maintenance, and future development.

When UserFrosting 5.0 was released, the path was clear : Finish the features that missed the release in 5.1, then move on to 6.0 for the complete frontend rewrite. Now that 6.0 is here, the focus shift once again, but in a different way. Both 5.1 and 6.0 where the necessary steps to get to the new architecture, but now that it's in place, the focus will be on improving and expanding it in a big way : **Add new features**.

These new features where blocked by the unorganized backend and the dated frontend, But now that we have a solid foundation, we can finally start building the features that were planned for years, as well as many new ones that were not even on the roadmap yet. The next few months will see a lot of new features being added to UserFrosting 6.0, and the pace of development will be much faster than it was during the last development cycles. And with Copilot and AI tools, the development process will be more efficient than ever, allowing us to deliver new features and improvements at a much faster pace.

These features are planned to be added in minor versions (6.1, 6.2, etc.) and will be fully backward compatible with 6.0, so you can upgrade to them as soon as they are released without worrying about breaking changes. No new major versions (e.g. 7.0) is planned on the short term.

## Planned Features & Improvements

> [!NOTE]
> The following are **potential future features** being considered. They are not commitments, and priorities may change based on community feedback and maintainer availability.

### Short-Term

- Complete frontend test coverage
- New demo apps showcasing best practices, advanced features and use cases for UserFrosting 6.0
- Rewrite of the Activity Log, including a new UI and more detailed logging
- Upgrade dependencies, especially Laravel dependencies
- Various UI improvements and new components in the Pink-Cupcake theme
- Various performance optimizations and bug fixes based on user feedback

### Mid-Term
- 2FA when logging, using the new OTP (One-Time Password) support and adding TOTP and HOTP algorithms with QR code generation and backup codes
- New permissions system with more granular control and better UI for managing permissions

## Version Support Policy

The current version support policy is as follows:

### UserFrosting 6.0 (Current)

**Status**: Active Development & Support

- **New Features**: Regular feature additions
- **Bug Fixes**: Prompt bug fixes and improvements
- **Security Updates**: Immediate security patches
- **Documentation**: Continuously updated and improved
- **Community Support**: Active help via chat and GitHub

**Recommended For**: All new projects and actively maintained applications.

### UserFrosting 5.1 (Legacy)

**Status**: Security Maintenance Only

- **New Features**: ❌ No new features
- **Bug Fixes**: ⚠️ Critical bugs only
- **Security Updates**: ✅ Security patches as required
- **Documentation**: 🔒 Frozen (available at [learn.userfrosting.com/5.1](https://learn.userfrosting.com/5.1))
- **Community Support**: ⚠️ Limited, focus shifted to 6.0

**End of Life**: TBD (dependent on security needs and user base)

**Recommended For**: Stable production applications that don't need new features and can't allocate migration time.

### Upgrading Between Versions

The goal for minor version upgrades (e.g., 6.0 → 6.1) is to provide new features and improvements while maintaining backward compatibility. These should be straightforward to upgrade, with clear documentation and minimal code changes required.

**Process**:
```bash
# Update dependencies
composer update
npm update

# Run any new migrations
php bakery migrate

# Rebuild assets
npm run vite:build

# Clear caches
php bakery clear-cache
```

The UserFrosting team is committed to making this the best PHP framework for user-centered applications. Thank you for being part of the journey! 🚀
