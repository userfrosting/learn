---
title: What to Expect After 6.0
description: Future roadmap and plans for UserFrosting beyond version 6.0
wip: true
---

# What to Expect After UserFrosting 6.0

UserFrosting 6.0 represents a major milestone in the framework's evolution, but it's not the end of the journey. This page outlines what you can expect in terms of updates, maintenance, and future development.

## Version Support Policy

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
- **Security Updates**: ✅ Security patches for 12-18 months from 6.0 release
- **Documentation**: 🔒 Frozen (available at [learn.userfrosting.com/5.1](https://learn.userfrosting.com/5.1))
- **Community Support**: ⚠️ Limited, focus shifted to 6.0

**End of Life**: Approximately 12-18 months after UserFrosting 6.0 stable release.

**Recommended For**: Stable production applications that don't need new features and can't allocate migration time.

## Release Cadence

### Minor Releases (6.1, 6.2, etc.)

**Frequency**: Every 3-4 months

**What to Expect**:
- New features that don't break backward compatibility
- Performance improvements
- Developer experience enhancements
- Additional documentation and examples
- Dependency updates (within semver constraints)

**Example Features**:
- Additional Vue 3 components
- New Bakery CLI commands
- Enhanced debugging tools
- More Vite plugins and optimizations
- Improved testing utilities

### Patch Releases (6.0.1, 6.0.2, etc.)

**Frequency**: As needed (typically every 2-4 weeks)

**What to Expect**:
- Bug fixes
- Security patches
- Documentation corrections
- Dependency security updates
- Performance optimizations

## Planned Features & Improvements

> [!NOTE]
> The following are **potential future features** being considered. They are not commitments, and priorities may change based on community feedback and maintainer availability.

### Short-Term (Next 6 Months)

#### Enhanced Vue 3 Components Library

**Status**: Under Development

**Description**: A comprehensive set of pre-built Vue 3 components for common UserFrosting patterns:
- Data tables with sorting, filtering, pagination
- Form builders with validation
- Modal dialogs
- Alert/notification system
- User pickers and role selectors

**Why**: Reduce boilerplate and speed up development.

#### Improved TypeScript Support

**Status**: Planned

**Description**:
- Complete type definitions for all UserFrosting services
- Type-safe API client generation
- Better IDE autocomplete and error checking
- Stricter type checking in core components

**Why**: Catch errors earlier and improve developer experience.

#### Testing Tools & Examples

**Status**: In Progress

**Description**:
- More comprehensive testing documentation
- Example test suites for common scenarios
- Better factory definitions
- Integration test helpers
- Frontend testing with Vitest

**Why**: Make testing easier and encourage test-driven development.

### Mid-Term (6-12 Months)

#### GraphQL API Option

**Status**: Exploring

**Description**: Optional GraphQL API layer alongside REST:
- Type-safe queries and mutations
- Efficient data fetching (no over/under-fetching)
- Better support for complex frontend applications
- Subscriptions for real-time features

**Why**: Modern alternative to REST for complex data requirements.

#### Enhanced CLI Tools

**Status**: Planned

**Description**:
- Interactive sprinkle generator (`bakery make:sprinkle`)
- Component scaffolding (`bakery make:vue-component`)
- Migration helpers (`bakery make:migration`)
- Better error messages and debugging output

**Why**: Speed up common development tasks.

#### Performance Monitoring

**Status**: Exploring

**Description**:
- Built-in performance profiling
- Database query logging and analysis
- Asset loading optimization suggestions
- Production monitoring hooks

**Why**: Help developers identify and fix performance bottlenecks.

### Long-Term (12+ Months)

#### Headless CMS Mode

**Status**: Concept

**Description**: Optional headless mode where UserFrosting acts as a pure API backend:
- Decoupled frontend deployment
- Mobile app support
- Third-party integration
- Multi-frontend architecture

**Why**: Support modern application architectures.

#### Plugin Marketplace

**Status**: Concept

**Description**: A curated marketplace for UserFrosting extensions:
- Vetted third-party sprinkles
- Easy installation and updates
- Quality ratings and reviews
- Commercial and free options

**Why**: Foster ecosystem growth and make extending UserFrosting easier.

#### Multi-Tenancy Support

**Status**: Concept

**Description**: Built-in support for multi-tenant applications:
- Tenant isolation
- Shared or separate databases
- Per-tenant customization
- Tenant-aware routing and assets

**Why**: Support SaaS applications built with UserFrosting.

## Breaking Changes Policy

UserFrosting follows [Semantic Versioning](https://semver.org/):

### Major Versions (7.0, 8.0, etc.)

**Frequency**: Every 2-3 years

**May Include**:
- Breaking API changes
- Removal of deprecated features
- Major architectural changes
- Updated system requirements
- New best practices

**Migration**: Comprehensive upgrade guides provided.

### Minor Versions (6.1, 6.2, etc.)

**Frequency**: Every 3-4 months

**Guarantees**:
- ✅ Backward compatible
- ✅ Safe to upgrade
- ✅ Deprecations may be introduced (with warnings)
- ✅ No breaking changes

**Migration**: Usually drop-in replacement, minimal changes needed.

### Patch Versions (6.0.1, 6.0.2, etc.)

**Frequency**: As needed

**Guarantees**:
- ✅ Bug fixes only
- ✅ No new features
- ✅ No API changes
- ✅ Safe to upgrade immediately

**Migration**: No code changes required.

## Deprecation Strategy

When features are deprecated:

1. **Announcement**: Deprecated features are clearly marked in documentation
2. **Warning Period**: At least one minor version before removal
3. **Alternatives**: Migration path provided to new approach
4. **Support**: Deprecated features continue working until next major version

**Example Timeline**:
- **6.0**: Feature X is current
- **6.1**: Feature X deprecated, Feature Y introduced as replacement
- **6.2-6.x**: Both Feature X and Y available, warnings in logs
- **7.0**: Feature X removed, Feature Y is the standard

## Community Involvement

UserFrosting is open-source and community-driven. You can influence the roadmap:

### Contribute Code

- Submit pull requests to [userfrosting/monorepo](https://github.com/userfrosting/monorepo)
- Fix bugs, add features, improve documentation
- Review and test pull requests from others

### Provide Feedback

- Report bugs and request features on [GitHub Issues](https://github.com/userfrosting/monorepo/issues)
- Participate in discussions on [chat.userfrosting.com](https://chat.userfrosting.com)
- Vote on feature requests and proposals

### Support Development

- [Donate on Ko-fi](https://ko-fi.com/lcharette)
- [Become a sponsor on Open Collective](https://opencollective.com/userfrosting)
- Hire core team members for consulting/development

### Spread the Word

- Share your UserFrosting projects
- Write tutorials and blog posts
- Answer questions from other users
- Rate and review UserFrosting

## Staying Updated

### Announcements

Official announcements are posted to:
- [GitHub Releases](https://github.com/userfrosting/monorepo/releases)
- [UserFrosting Blog](https://www.userfrosting.com/blog/) (when available)
- [Community Chat](https://chat.userfrosting.com)

### Newsletter

Sign up for the UserFrosting newsletter (when available) to receive:
- Major version announcements
- Security advisories
- Featured community projects
- Tips and tutorials

### Social Media

Follow UserFrosting on social media for updates:
- Twitter/X: [@UserFrosting](https://twitter.com/userfrosting) (if available)
- GitHub: [userfrosting](https://github.com/userfrosting)

## Upgrading Between Versions

### Minor Version Upgrades (e.g., 6.0 → 6.1)

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
php bakery cache:clear
```

**Expected Time**: 15-30 minutes

**Risk Level**: Low - backward compatible

### Major Version Upgrades (e.g., 6.x → 7.0)

**Process**:
- Comprehensive upgrade guide provided
- Review breaking changes
- Update custom code
- Test thoroughly

**Expected Time**: Several hours to several days (depending on customization)

**Risk Level**: Medium to High - breaking changes likely

## Framework Philosophy

As UserFrosting continues to evolve, these principles guide development:

### Modern But Pragmatic

We embrace modern tools and practices, but only when they provide clear benefits:
- ✅ Adopt proven technologies (Vue 3, Vite, TypeScript)
- ⚠️ Be cautious with bleeding-edge features
- ❌ Avoid "hype-driven development"

### Backward Compatibility

We take backward compatibility seriously:
- Breaking changes only in major versions
- Clear migration paths provided
- Deprecated features supported for reasonable periods

### Developer Experience

We prioritize developer happiness:
- Fast development builds (Vite)
- Clear error messages
- Comprehensive documentation
- Active community support

### Educational Focus

UserFrosting aims to teach best practices:
- Documentation explains the "why" not just the "how"
- Examples demonstrate modern PHP/JavaScript patterns
- Comments in code help developers learn

## Questions?

Have questions about the future of UserFrosting?

- **Technical Questions**: Ask in [chat.userfrosting.com](https://chat.userfrosting.com)
- **Feature Requests**: Open an issue on [GitHub](https://github.com/userfrosting/monorepo/issues)
- **General Discussion**: Start a thread in the community chat

The UserFrosting team is committed to making this the best PHP framework for user-centered applications. Thank you for being part of the journey! 🚀
