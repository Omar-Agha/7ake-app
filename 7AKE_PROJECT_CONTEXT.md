# 7AKE — Project Context and Business Rules

## 1. Purpose
7AKE (حَكِي) is a clothing brand selling simple, thoughtfully designed apparel, initially hoodies and T-shirts. Products feature short Arabic or English phrases, lyrics-inspired concepts, and minimal artwork. The brand identity is understated, expressive, and text-led.

This document explains the intended business behavior of the backend. It is a shared reference for developers and coding assistants, not an implementation specification. Do not treat undecided details as established requirements.

## 2. Product and System Scope
- Customers browse products, select a specific size/color variant, and eventually purchase through a website or Flutter app.
- Staff manage the catalog, stock, and later orders through an admin panel.
- The backend is a Laravel API-first modular monolith. Filament provides the admin interface. The website and Flutter app are separate API clients.
- Business rules belong to backend modules, not to Filament pages or individual client applications.
- Start small. Do not add complex enterprise features without a real business requirement.

## 3. Modules
### Catalog — what we sell
**Entities:** Product, ProductVariant, Category, Collection.

- **Product:** The customer-facing item, such as a hoodie featuring a particular design. It contains shared product information.
- **ProductVariant:** A purchasable version of a product, typically a particular color and size. Each variant is distinct for inventory purposes.
- **Category:** Classifies what the product is (e.g., Hoodie or T-shirt). Initially, each product has one primary category; a category has many products.
- **Collection:** Optional merchandising grouping (e.g., music-inspired designs or a seasonal collection). Products and collections have a many-to-many relationship. A product may belong to no collections.

**Rules:**
- A product can have multiple variants; inventory is tracked by variant, never by the product as a whole.
- Draft, Published, and Archived are useful product lifecycle states. Published does not guarantee stock availability.
- Keep historical product/order information; avoid destructive deletion of items referenced by transactions.
- A SKU uniquely identifies a variant, but its generation policy is **not yet decided**.
- A separate reusable Design entity and advanced pricing logic are **deferred**.

### Inventory — what is physically available and temporarily held
**Entities:** InventoryItem, StockMovement, Reservation.

- **InventoryItem:** Current stock state for one product variant. Holds `on_hand_quantity` and `reserved_quantity`. Available quantity is `on_hand_quantity - reserved_quantity`.
- **StockMovement:** Immutable audit record of a committed change to on-hand stock. A signed quantity indicates increase or decrease; a reason explains why. Initial reasons: RESTOCK, SALE, RETURN, DAMAGE, ADJUSTMENT. Reservation and release are **not** stock movements.
- **Reservation:** A temporary claim against available stock, recording quantity, lifecycle status, optional expiration deadline, and the business operation that owns the claim.

**Rules:**
- One inventory item per variant for the initial single-location model.
- `reserved_quantity` is a maintained summary of outstanding reservations, allowing fast availability checks without summing reservation records on every read.
- Reservation creation, release, expiration processing, and conversion to a sale must update the reservation and inventory counters **atomically** and safely under concurrency.
- Normal selling/reservation operations must not oversell: reserved stock cannot exceed on-hand stock. Stock discrepancies discovered during physical counts require explicit handling rather than silent inconsistent adjustments.
- Confirming a sale releases its reservation, reduces on-hand stock, and records a SALE movement as one consistent operation.
- Releasing or expiring a reservation decreases reserved quantity without changing on-hand stock or creating a stock movement.
- Reservation lifecycle states currently proposed: ACTIVE, CONFIRMED, RELEASED, EXPIRED. `expires_at` is the validity deadline; status records the processed lifecycle transition. A passed deadline must not allow a late checkout to confirm merely because cleanup has not run yet.
- A scheduled process may expire outstanding reservations. Concurrent expiration and confirmation must not both succeed.
- Stock movements should identify their originating business action (e.g., order, return, stock adjustment). The exact reference representation (`reference_type`/`reference_id` or another approach) is **not yet finalized**.
- Stock movement history is not edited to fix mistakes; use corrective movements.
- No multi-warehouse or supplier/purchase-order system in the initial scope.

### Future modules — planned, not designed yet
- **Customers:** Customer identity, contact details, and account-related information.
- **Cart:** Customer selections before checkout. Adding to cart does **not automatically** reserve stock.
- **Orders:** Purchased items, order lifecycle, totals, and fulfillment state.
- **Payments:** Payment initiation, confirmation, failure, and reconciliation with payment providers.
- **Shipping:** Delivery details, fulfillment, and shipment tracking as required.
- **Notifications:** Order and customer communications as needed.

These are **planned capabilities**, not finalized entity lists or workflows. Define their entities and rules when each module is designed.

## 4. Cross-Module Principles
- Catalog owns product/variant definitions; Inventory owns stock and reservation behavior; Orders and Payments will own their respective business lifecycles.
- Preserve traceability between stock changes and the business event that caused them.
- Treat checkout, payment callbacks, retries, and reservation expiration as concurrency-sensitive operations. Avoid duplicate effects.
- Business invariants must hold regardless of whether an action originates from Filament, the website, the mobile app, or a background job.
- Prefer clear boundaries and simple business operations over premature abstraction.

## 5. Decisions Still Open
1. Exact checkout stage at which inventory is reserved, and how long reservations last.
2. Reservation ownership/reference structure and final lifecycle transitions.
3. SKU format and generation rules.
4. Pricing, discounts, taxes, currencies, and shipping policies.
5. Order/payment workflows and cancellation/return rules.
6. Exact product media and design reuse requirements.

When implementing an open area, propose the smallest reasonable design and request a decision rather than silently inventing business policy.

## 6. Guidance for Codex
- Read this file before proposing architecture or implementing business features.
- Keep the agreed entities and responsibilities intact unless a change is explicitly discussed.
- Implement one module or business operation at a time.
- Explain proposed business assumptions and important trade-offs before expanding scope.
- Do not introduce new models, workflows, or integrations just because they are common in e-commerce.
- Keep domain behavior independent from UI/admin frameworks.
- Treat this document as the current project context; update it when business decisions change.
