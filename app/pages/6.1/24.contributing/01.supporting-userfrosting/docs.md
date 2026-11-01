---
title: Supporting UserFrosting
description: Donate your time, money, or expertise to furthering the development of UserFrosting.
---

## Contributing time

Our [chat room](https://chat.userfrosting.com) welcomes users from all over the world seeking help 24/7. The core development team appreciates community members who help answer questions and support fellow users.

If you feel comfortable with UserFrosting, consider keeping a browser tab open to our [chat room](https://chat.userfrosting.com) and helping other users when you have time. Your assistance is invaluable to the community!

## Contributing code and content

We welcome pull requests! Based on your skills and interests, you might contribute:

- **Documentation improvements** - Fix typos, clarify explanations, add examples
- **Language translations** - Help internationalize UserFrosting
- **Bug fixes** - Resolve issues and improve stability
- **Feature enhancements** - Implement requested features (after discussion)
- **Testing** - Write or improve unit/integration tests

> [!NOTE]
> **Before starting work on a new feature**, discuss it with the dev team in our [chat room](https://chat.userfrosting.com) or [GitHub Discussions](https://github.com/userfrosting/UserFrosting/discussions). This ensures your contribution aligns with the project's vision and technical requirements.
> 
> UserFrosting is fully modular - additional features might be better implemented as a custom [Sprinkle](/sprinkles) rather than added to the core framework. Join us in chat to discuss the best approach for your feature.

### Style and Coding Standards

All contributions should follow these standards:

- **PHP**: Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding style standard
- **TypeScript/JavaScript**: Follow project conventions (check existing code)
- **Vue Components**: Use Vue 3 Composition API patterns
- **CSS**: Follow the project's existing styling patterns
- **Documentation**: Use clear, concise language with proper Markdown formatting

Consider using automated tools:
- **PHP**: [PHP CS Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) and [PHPStan](https://phpstan.org/)
- **Frontend**: ESLint and Prettier (configured in the project)

### Contributing to the Monorepo

UserFrosting uses a centralized [monorepo](https://github.com/userfrosting/monorepo) for core development. This repository contains the framework and core sprinkles.

**Pull request guidelines:**

1. **Fork** the repository and create a feature branch from `main`
2. **Write clear commit messages** describing your changes
3. **Test your changes** - add or update tests as needed
4. **Update documentation** if you're changing public APIs
5. **Submit a pull request** against the `main` branch
6. **Update CHANGELOG.md** with your changes

**Branch naming conventions:**
- `feature/description` - New features
- `fix/description` - Bug fixes
- `docs/description` - Documentation updates
- `refactor/description` - Code refactoring

> [!TIP]
> Before submitting your pull request, make sure all tests pass and there are no linting errors. Run `composer test` and `npm run lint` to verify.

## Contributing to Documentation

Documentation lives in the [learn repository](https://github.com/userfrosting/learn). To contribute:

1. Fork the repository
2. Edit Markdown files in `app/pages/{version}/`
3. Follow the [documentation style guide](#documentation-style)
4. Test locally by running the documentation site
5. Submit a pull request

**Documentation best practices:**
- Use clear, educational language aimed at varying skill levels
- Include practical code examples
- Add images where they clarify concepts (store in `app/pages/{version}/images/`)
- Cross-reference related pages
- Test all code snippets to ensure accuracy

## Financial support

UserFrosting is completely free and open source, and licensed under the [MIT License](https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md).

That being said, yes, we'll take your money!

Your financial contribution will help keep our chat and demo servers going. And, who knows? Maybe enough people will donate and we can make UserFrosting our full-time job ;-)

The easiest way to contribute financially is through [Open Collective](https://opencollective.com/userfrosting) or [Ko-fi](https://ko-fi.com/lcharette).

You can also help pay for our web and chat server costs by signing up with DigitalOcean using our [referral link](https://m.do.co/c/833058cf3824). Once you've spent $25 with them, we'll earn $25 towards our own DigitalOcean account.

## Code of Conduct

UserFrosting follows a [Code of Conduct](https://github.com/userfrosting/UserFrosting/blob/main/CODE_OF_CONDUCT.md) to ensure a welcoming, inclusive community. By participating, you agree to uphold these standards.

## Questions?

If you have questions about contributing:

- Join our [chat room](https://chat.userfrosting.com)
- Start a [GitHub Discussion](https://github.com/userfrosting/UserFrosting/discussions)
- Check the [contributing guidelines](https://github.com/userfrosting/UserFrosting/blob/main/CONTRIBUTING.md) in the repository

Thank you for helping make UserFrosting better! 🙏
