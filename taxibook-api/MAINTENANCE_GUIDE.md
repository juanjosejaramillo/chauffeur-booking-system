# Documentation Maintenance Guide

## Overview
This guide explains how to maintain and update the documentation when implementing new features, fixing bugs, or making changes to the LuxRide Chauffeur Booking System.

## Documentation Structure

### Core Documentation Files
1. **CLAUDE.md** - Main project context (update for major changes)
2. **ARCHITECTURE.md** - Technical architecture (update for structural changes)
3. **API_REFERENCE.md** - API documentation (update for new endpoints)
4. **DATABASE_SCHEMA.md** - Database structure (update for schema changes)
5. **DEPLOYMENT.md** - Deployment procedures (update for deployment changes)
6. **FEATURES.md** - Feature documentation (update for new features)
7. **TROUBLESHOOTING.md** - Common issues (add new problems/solutions)
8. **TODO.md** - Task tracking (update regularly)
9. **CHANGELOG.md** - Version history (update for each release)
10. **.clauderules** - AI assistant rules (update for new patterns)
11. **TESTING.md** - Testing guidelines (update for new test strategies)
12. **MAINTENANCE_GUIDE.md** - This file (update as needed)

## When to Update Documentation

### Immediate Updates Required
These changes require immediate documentation updates:

1. **New API Endpoints**
   - Update `API_REFERENCE.md` with endpoint details
   - Add to `CHANGELOG.md`
   - Update `TESTING.md` if tests added

2. **Database Schema Changes**
   - Update `DATABASE_SCHEMA.md` with new tables/columns
   - Update `CHANGELOG.md` with migration notes
   - Update `DEPLOYMENT.md` if deployment steps change

3. **New Features**
   - Update `FEATURES.md` with feature description
   - Update `CLAUDE.md` if it's a major feature
   - Update `CHANGELOG.md` with feature announcement
   - Move completed items from `TODO.md`

4. **Breaking Changes**
   - Update `CHANGELOG.md` with breaking change notice
   - Update `API_REFERENCE.md` if API changes
   - Update `DEPLOYMENT.md` with migration steps

5. **Security Updates**
   - Update `CHANGELOG.md` immediately
   - Update `TROUBLESHOOTING.md` if needed
   - Update `.clauderules` with new security requirements

### Regular Updates (Weekly/Sprint)
1. Review and update `TODO.md`
2. Add new troubleshooting entries
3. Update test documentation
4. Review and refine existing docs

## Update Procedures by File

### CLAUDE.md
**When to update**: Major features, architecture changes, new integrations

```markdown
## Example Update
After adding SMS notifications:

### Third-Party Integrations
- **SMS**: Twilio API (SMS notifications) // ADD THIS LINE

## Key Features
### 6. Notification System
#### SMS Notifications // ADD THIS SECTION
- Booking confirmations via SMS
- Driver arrival notifications
- Configurable SMS preferences
```

### API_REFERENCE.md
**When to update**: New endpoints, parameter changes, response format changes

```markdown
## Example Update
After adding a new endpoint:

### Send SMS Notification
**Endpoint**: `POST /api/sms/send`
**Description**: Send SMS notification to customer
**Authentication**: Required (Admin)

**Request Body**:
{
  "phone": "+1234567890",
  "message": "Your driver has arrived"
}

**Response**: 
{
  "success": true,
  "message_id": "msg_123456"
}
```

### DATABASE_SCHEMA.md
**When to update**: New tables, column additions, relationship changes

```markdown
## Example Update
After adding SMS logs table:

### 13. **sms_logs**
SMS notification history.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| booking_id | bigint | Foreign key to bookings |
| phone | varchar(20) | Recipient phone |
| message | text | SMS content |
| status | enum | pending, sent, failed |
| sent_at | timestamp | When sent |
| created_at | timestamp | Creation date |
```

### FEATURES.md
**When to update**: New features, feature enhancements, feature removals

```markdown
## Example Update
After adding SMS feature:

### 8. SMS Notification System

#### Features
- Automated booking confirmations
- Driver arrival notifications
- Two-way messaging (future)
- Delivery status tracking

#### Configuration
- Twilio account required
- Configure in admin settings
- Set notification preferences per booking
```

### CHANGELOG.md
**When to update**: Every release, hotfix, or significant change

```markdown
## Example Update

## [1.4.0] - 2025-01-20

### Added
- SMS notification system via Twilio
- Two-factor authentication for admin
- Bulk booking import feature

### Fixed
- Fixed timezone issue in booking reminders
- Resolved memory leak in email queue

### Changed
- Improved performance of booking list queries
- Updated Stripe SDK to v18.0
```

### TODO.md
**When to update**: Task completion, new requirements, priority changes

```markdown
## Example Update
Move completed items to "Completed" section:

## Completed ✅
- [x] SMS notification system // MOVED FROM HIGH PRIORITY
- [x] Two-factor authentication // MOVED FROM MEDIUM PRIORITY
```

## Automation with Prompts

### After Implementing a New Feature
Use this prompt template:

```
I've just implemented [FEATURE NAME]. Please update the following documentation files:
1. Add the feature to FEATURES.md
2. Update CHANGELOG.md with version [VERSION]
3. Add any new API endpoints to API_REFERENCE.md
4. Update DATABASE_SCHEMA.md if there were schema changes
5. Move the completed task from TODO.md to the Completed section
6. Update CLAUDE.md if this is a major feature

Feature details:
- Description: [DESCRIPTION]
- API endpoints added: [ENDPOINTS]
- Database changes: [CHANGES]
- Configuration required: [CONFIG]
```

### After Fixing a Bug
Use this prompt:

```
I've fixed a bug: [BUG DESCRIPTION]. Please:
1. Add the fix to CHANGELOG.md under "Fixed"
2. If it's a common issue, add the solution to TROUBLESHOOTING.md
3. Update TESTING.md if new tests were added

Bug details:
- Issue: [WHAT WAS BROKEN]
- Solution: [HOW IT WAS FIXED]
- Affected versions: [VERSIONS]
```

### After Database Migration
Use this prompt:

```
I've created a new migration: [MIGRATION NAME]. Please:
1. Update DATABASE_SCHEMA.md with the schema changes
2. Add migration notes to CHANGELOG.md
3. Update DEPLOYMENT.md if deployment steps changed
4. Update ARCHITECTURE.md if relationships changed

Migration details:
- Tables added: [TABLES]
- Columns added: [COLUMNS]
- Relationships: [RELATIONSHIPS]
- Migration file: [FILE NAME]
```

## Documentation Standards

### Markdown Formatting
```markdown
# Main Title (H1 - one per file)
## Section Title (H2)
### Subsection (H3)
#### Detail Level (H4)

**Bold** for emphasis
`code` for inline code
```code blocks``` for multi-line code

| Table | Headers |
|-------|---------|
| Data  | Values  |

- Bullet points for lists
1. Numbered lists for steps

> Blockquotes for important notes
```

### Code Examples
Always include practical examples:

```php
// Good - includes context and explanation
/**
 * Send SMS notification to customer
 * Uses Twilio API with retry logic
 */
public function sendSMS(string $phone, string $message): bool
{
    try {
        return $this->twilioService->send($phone, $message);
    } catch (Exception $e) {
        Log::error('SMS failed', ['error' => $e->getMessage()]);
        return false;
    }
}
```

### Version Numbering
Follow Semantic Versioning:
- **Major (X.0.0)**: Breaking changes
- **Minor (0.X.0)**: New features, backwards compatible
- **Patch (0.0.X)**: Bug fixes

## Review Checklist

### Before Committing
- [ ] Is CHANGELOG.md updated?
- [ ] Are new features in FEATURES.md?
- [ ] Are API changes in API_REFERENCE.md?
- [ ] Are schema changes in DATABASE_SCHEMA.md?
- [ ] Is TODO.md current?
- [ ] Do examples work?
- [ ] Are security considerations noted?

### Weekly Review
- [ ] Review TODO.md priorities
- [ ] Check for undocumented features
- [ ] Update troubleshooting entries
- [ ] Verify documentation accuracy
- [ ] Remove outdated information

### Monthly Review
- [ ] Full documentation audit
- [ ] Update architecture diagrams
- [ ] Review and update examples
- [ ] Check external links
- [ ] Update version roadmap

## Common Documentation Tasks

### Adding a New Service
1. Update `ARCHITECTURE.md` with service description
2. Add service to `CLAUDE.md` quick reference
3. Document methods in code with PHPDoc
4. Add usage examples to relevant docs

### Adding a Configuration Option
1. Update `DEPLOYMENT.md` with new config
2. Add to admin settings if applicable
3. Document in `FEATURES.md` if user-facing
4. Add default value to `.env.example`

### Deprecating a Feature
1. Mark as deprecated in `CHANGELOG.md`
2. Add deprecation notice in `API_REFERENCE.md`
3. Update `TODO.md` with removal timeline
4. Document migration path in `DEPLOYMENT.md`

## Documentation Tools

### Generate API Docs from Code
```bash
# Generate API documentation (future)
php artisan api:docs

# Generate model documentation
php artisan model:docs
```

### Check Documentation
```bash
# Check for broken links
npm run check-links

# Validate markdown
npm run lint-md

# Check for outdated dependencies
npm outdated
composer outdated
```

## Best Practices

### Do's
✅ Update documentation immediately after changes
✅ Include code examples
✅ Keep language clear and concise
✅ Version all changes
✅ Test examples before documenting
✅ Include troubleshooting for common issues
✅ Cross-reference related documentation

### Don'ts
❌ Don't leave placeholders (TODO, TBD)
❌ Don't document future/planned features as complete
❌ Don't include sensitive information (passwords, keys)
❌ Don't copy-paste without reviewing
❌ Don't use ambiguous language
❌ Don't forget to update the changelog

## Quick Commands for Updates

### Update Multiple Files
```bash
# After adding a feature
echo "Remember to update: FEATURES.md, CHANGELOG.md, TODO.md"

# After API changes
echo "Remember to update: API_REFERENCE.md, CHANGELOG.md, TESTING.md"

# After database changes
echo "Remember to update: DATABASE_SCHEMA.md, CHANGELOG.md, DEPLOYMENT.md"
```

### Documentation Templates

Save these as snippets for quick insertion:

```markdown
<!-- New Feature Template -->
### [Feature Name]

#### Description
[Brief description of the feature]

#### How It Works
1. [Step 1]
2. [Step 2]
3. [Step 3]

#### Configuration
- Setting 1: [Description]
- Setting 2: [Description]

#### API Endpoints
- `GET /api/[endpoint]` - [Description]
- `POST /api/[endpoint]` - [Description]

#### Examples
```code
[Example code]
```

#### Troubleshooting
- Issue: [Problem]
  Solution: [Fix]
```

## Maintenance Schedule

### Daily
- Update TODO.md with completed tasks
- Note any bugs in TROUBLESHOOTING.md

### Weekly
- Review and update CHANGELOG.md
- Check documentation accuracy
- Update feature documentation

### Sprint/Release
- Complete CHANGELOG.md entry
- Update version numbers
- Review all documentation
- Archive old documentation if needed

### Quarterly
- Full documentation audit
- Update architecture diagrams
- Review and update all examples
- Plan documentation improvements

## Getting Help

If you're unsure about documentation updates:
1. Check existing documentation for patterns
2. Review recent commits for examples
3. Ask: "What would a new developer need to know?"
4. Include examples and edge cases
5. Test your documentation by following it

## Final Notes

Remember: **Good documentation is an investment in the project's future.** Every minute spent on clear documentation saves hours of confusion later.

When in doubt, over-document rather than under-document. Future developers (including yourself) will thank you!