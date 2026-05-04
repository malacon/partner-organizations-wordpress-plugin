# Partner Directory

This context defines the language for a WordPress plugin that lets administrators manage and publish a directory of partner organizations.

## Language

**Partner Organization**:
An external organization listed in the directory with identifying details, an optional logo, and an optional public website.
_Avoid_: Partner, organization catalog item, listing

**Partner Directory**:
The public-facing collection of Partner Organizations displayed on the website, optionally filtered by Partner Category.
_Avoid_: Catalog, database, registry

**Partner Category**:
A classification assigned to Partner Organizations for grouping and filtering the directory.
_Avoid_: Type, tag, vertical

## Relationships

- A **Partner Directory** contains zero or more **Partner Organizations**.
- A **Partner Organization** appears in the **Partner Directory** when it is published.
- A **Partner Organization** has zero or one **Partner Category**.

## Example dialogue

> **Dev:** "Should a draft **Partner Organization** appear in the **Partner Directory**?"
> **Domain expert:** "No — only published **Partner Organizations** should be shown publicly."

## Flagged ambiguities

- "partner" can mean either the WordPress post type slug or the domain concept; resolved: use **Partner Organization** for the domain concept.
