# 📊 Visual State Machine Diagrams & Reference

## Main Delivery Lifecycle

```
┌─────────────────────────────────────────────────────────────────┐
│                     DELIVERY ORDER LIFECYCLE                     │
└─────────────────────────────────────────────────────────────────┘

    ┌──────────┐
    │  DRAFT   │  Order created but not confirmed
    └────┬─────┘
         │ customer confirms
         ↓
    ┌──────────────────┐
    │ AWAITING_COURIER │  Waiting for pickup (NO courier assigned)
    └────┬──────────┬──┘
         │          │
         │          └─── system timeout → EXPIRED (terminal)
         │               (after 24h no acceptance)
         │
         │ courier accepts
         ↓
    ┌──────────┐
    │ ACCEPTED │  Courier assigned, going to pickup
    └────┬──────┘
         │ heading to pickup
         ↓
    ┌────────────────────┐
    │ ARRIVING_AT_PICKUP │  On the way to pickup location
    └────┬───────────────┘
         │ arrived
         ↓
    ┌──────────────┐
    │  AT_PICKUP   │  At pickup location, collecting item
    └────┬─────────┘
         │ item collected
         ↓
    ┌──────────────┐
    │  PICKED_UP   │  Item in courier's possession
    └────┬─────────┘
         │ starting delivery
         ↓
    ┌────────────┐
    │ IN_TRANSIT │  Traveling to delivery location
    └────┬───────┘
         │ approaching destination
         ↓
    ┌──────────────────────┐
    │ ARRIVING_AT_DROPOFF  │  Near delivery location
    └────┬─────────────────┘
         │ arrived
         ↓
    ┌─────────────┐
    │ AT_DROPOFF  │  At delivery location
    └────┬────────┘
         │
         ├─ delivery successful → DELIVERED ┐ (terminal)
         │                                   │
         └─ delivery failed          → DELIVERY_FAILED
                                          ↓
                                     RETURNED (terminal)

TERMINAL STATES (No further transitions):
├─ DELIVERED ✓ (success)
├─ CANCELLED_BY_USER ✗ (user cancelled)
├─ CANCELLED_BY_COURIER ✗ (courier cancelled)
├─ CANCELLED_BY_SYSTEM ✗ (system cancelled)
├─ EXPIRED ✗ (no acceptance timeout)
└─ RETURNED ✗ (failed delivery return)
```

---

## Cancellation Paths

```
┌─────────────────────────────────────────────────────────────────┐
│                    CANCELLATION RULES                            │
└─────────────────────────────────────────────────────────────────┘

USER CAN CANCEL:
┌──────────┐
│  DRAFT   │  ← Customer can cancel here (not posted yet)
└──────────┘
        │ confirm
        ↓
┌──────────────────┐
│ AWAITING_COURIER │  ← Customer can cancel here (no courier yet)
└──────────────────┘
        │ courier accepts
        ↓
┌──────────┐
│ ACCEPTED │  ← Customer can STILL cancel here (courier not at pickup yet)
└──────────┘
        │ courier heading to pickup
        ↓
┌────────────────────┐
│ ARRIVING_AT_PICKUP │  ← LOCKED! Customer CANNOT cancel
└────────────────────┘
        │ arrived at pickup
        ↓
┌──────────────┐
│  AT_PICKUP   │  ← LOCKED! Customer CANNOT cancel
└──────────────┘
        │ item collected
        ↓
┌──────────────┐
│  PICKED_UP   │  ← LOCKED! Customer CANNOT cancel (item already picked up!)
└──────────────┘


COURIER CAN CANCEL:
┌──────────┐
│ ACCEPTED │  ← Courier can cancel here (before heading to pickup)
└──────────┘
        │ heading to pickup
        ↓
┌────────────────────┐
│ ARRIVING_AT_PICKUP │  ← Courier can STILL cancel here
└────────────────────┘
        │ arrived
        ↓
┌──────────────┐
│  AT_PICKUP   │  ← Courier can STILL cancel here (not picked up yet)
└──────────────┘
        │ item collected
        ↓
┌──────────────┐
│  PICKED_UP   │  ← LOCKED! Courier CANNOT cancel (item already collected!)
└──────────────┘

If courier cancels before pickup:
   CANCELLED_BY_COURIER → Order returns to AWAITING_COURIER (other couriers can take it)
```

---

## State Transition Matrix

```
FROM\TO                    VALID TRANSITIONS                      TERMINAL?
─────────────────────────────────────────────────────────────────────────
draft                    → awaiting_courier, cancelled_by_user           NO
awaiting_courier         → accepted, cancelled_by_user, expired          NO
courier_assigned         → accepted, cancelled_by_system                 NO
accepted                 → arriving_at_pickup, cancelled_by_user,        NO
                          cancelled_by_courier
arriving_at_pickup       → at_pickup, cancelled_by_courier               NO
at_pickup                → picked_up, cancelled_by_courier               NO
picked_up                → in_transit, delivery_failed                   NO
in_transit               → arriving_at_dropoff, delivery_failed          NO
arriving_at_dropoff      → at_dropoff, delivery_failed                   NO
at_dropoff               → delivered, delivery_failed                    NO
delivered                → returned                                      YES ✓
delivery_failed          → returned, in_transit (retry)                  NO
returned                 → (none)                                        YES ✗
cancelled_by_user        → (none)                                        YES ✗
cancelled_by_courier     → (none)                                        YES ✗
cancelled_by_system      → (none)                                        YES ✗
expired                  → (none)                                        YES ✗
```

---

## Who Can Do What

```
┌─────────────────────────────────────────────────────────────────┐
│                    ACTION AUTHORIZATION MATRIX                   │
└─────────────────────────────────────────────────────────────────┘

ACTION                  CUSTOMER (USER)         COURIER             SYSTEM
─────────────────────────────────────────────────────────────────────────
Create order                   ✅                    ❌                ❌
Confirm order                  ✅                    ❌                ❌
   (draft→awaiting_courier)

Cancel before pickup           ✅                    ✅                ❌
   (user & courier cancellable)

Cancel after pickup            ❌                    ❌                ✅*
   (*system only for disputes)

Accept order                   ❌                    ✅                ❌
   (awaiting_courier→accepted) (first to click)

Mark as picked up              ❌                    ✅                ❌
Mark as delivered              ❌                    ✅                ❌
Record delivery failed         ❌                    ✅                ❌

Auto-expire order              ❌                    ❌                ✅
   (>24h in awaiting_courier)

Auto-cancel (rules)            ❌                    ❌                ✅
Auto-return (failed)           ❌                    ❌                ✅
```

---

## Database Interaction Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    DATABASE OPERATIONS                           │
└─────────────────────────────────────────────────────────────────┘

┌──────────────────────┐
│ delivery_orders      │
│ ─────────────────────│
│ id                   │
│ user_id (customer)   │
│ courier_id (nullable)│
│ status (17 values)   │
│ accepted_at          │
│ arriving_at_pickup_at
│ at_pickup_at         │
│ picked_up_at         │
│ arriving_at_dropoff_at
│ at_dropoff_at        │
│ delivered_at         │
│ delivery_failed_at   │
│ returned_at          │
│ expired_at           │
│ cancelled_at         │
│ cancellation_reason  │
│ created_at/updated_at
└──────────────────────┘
         ↓
┌──────────────────────────────┐
│ order_status_history         │
│ ──────────────────────────────│
│ id                           │
│ delivery_order_id (FK)       │
│ old_status                   │
│ new_status                   │
│ changed_by (user_id)         │
│ actor_type (user/courier/sys)│
│ reason                       │
│ notes                        │
│ location_lat/lng             │
│ created_at                   │
└──────────────────────────────┘

EXAMPLE HISTORY ENTRY:
┌─────────────────────────────────────────┐
| delivery_order_id: 100                  |
| old_status: 'awaiting_courier'          |
| new_status: 'accepted'                  |
| changed_by: 5 (courier user id)         |
| actor_type: 'courier'                   |
| reason: 'Courier accepted order'        |
| location_lat: '40.7128'                 |
| location_lng: '-74.0060'                |
| created_at: '2026-02-12 10:30:15'       |
└─────────────────────────────────────────┘
```

---

## Error Conditions & Resolution

```
┌─────────────────────────────────────────────────────────────────┐
│                    ERROR PREVENTION                              │
└─────────────────────────────────────────────────────────────────┘

ERROR                             CAUSE                   PREVENTION
─────────────────────────────────────────────────────────────────
Invalid transition               User tries wrong trans.  ALLOWED_TRANSITIONS
                                 (e.g., draft→delivered) map prevents

Double-booking                   Two couriers accept     DB::transaction()
                                 same order              + courier_id null check

Cancelling after pickup          User cancels after      isCancellableByUser()
                                 item collected          returns false

Chat without courier             Showing chat button     status !== 'draft' &&
                                 when no courier yet     isChatActive()

Status inference from            Code relies on          Status is source of
courier_id                       courier_id presence     truth only

Escaping terminal state          Trying to transition    Checks isTerminal()
                                 from delivered          before any transition

Race condition on courier         Multiple threads        DB::transaction()
assignment                       updating same order     with row locking
```

---

## Timestamp Coverage

```
                    Timeline of Order Delivery
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Created                created_at ──────────────┐
    ↓                                          │
DRAFT                  (no timestamp)          │
    │ confirm                                  │
    ↓                                          │
AWAITING_COURIER       (no timestamp)          │
    │ accept                                   │
    ↓                                          │
ACCEPTED               accepted_at ────────┐   │
    │ head to pickup                       │   │
    ↓                                      │   │
ARRIVING_AT_PICKUP     arriving_at_pickup_at  │
    │ arrive                                  │
    ↓                                      │   │
AT_PICKUP              at_pickup_at ────┐ │   │
    │ collect                            │ │   │
    ↓                                    │ │   │
PICKED_UP              picked_up_at ────┘ │   │
    │ travel                              │   │
    ↓                                      │   │
IN_TRANSIT             (no timestamp)      │   │
    │ approach                             │   │
    ↓                                      │   │
ARRIVING_AT_DROPOFF    arriving_at_dropoff_at │
    │ arrive                               │   │
    ↓                                      │   │
AT_DROPOFF             at_dropoff_at ─────┘   │
    │ success/failure                         │
    ↓                                         │
DELIVERED              delivered_at ──────────┤
DELIVERY_FAILED        delivery_failed_at ────┤
RETURNED               returned_at ───────────┤
CANCELLED_*            cancelled_at ──────────┤
EXPIRED                expired_at ────────────┤
                       updated_at ───────────┘

KEY: Each state can have its own timestamp for precise audit trail
```

---

## Quick Reference Card

```
╔═════════════════════════════════════════════════════════════════╗
║              QUICK REFERENCE - STATE MACHINE RULES              ║
╚═════════════════════════════════════════════════════════════════╝

✅ DO
├─ Check $order->isCancellableByUser() before user cancellation
├─ Check $order->isCancellableByCourier() before courier cancel
├─ Always use $order->transitionTo('status') for state changes
├─ Log all transitions with OrderStatusHistory
├─ Wrap courier operations in DB::transaction()
├─ Use $order->status as source of truth (not courier_id)
└─ Check $order->isTerminal() before any modification

❌ DON'T
├─ Manually set $order->status = 'something'
├─ Trust courier_id existence to infer state
├─ Allow transitions not in ALLOWED_TRANSITIONS
├─ Permit cancellation after picked_up status
├─ Skip validation before status changes
├─ Forget to log to OrderStatusHistory
└─ Assume order can still change if isTerminal() = true

📌 KEY METHODS
├─ transitionTo($status) - Validate & transition with timestamps
├─ canTransitionTo($status) - Check if transition allowed
├─ isCancellableByUser() - Can customer cancel?
├─ isCancellableByCourier() - Can courier cancel?
├─ isTerminal() - Is order in final state?
├─ isChatActive() - Should show chat?
└─ getValidTransitions() - What states can we go to?

🔒 RULES
├─ Terminal: delivered, cancelled_*, expired, returned
├─ User cancels: draft, awaiting_courier, accepted
├─ Courier cancels: accepted, arriving_at_pickup, at_pickup
├─ Auto transitions: transitionTo() auto-sets timestamps
├─ Race safety: All courier ops use DB::transaction()
└─ Audit trail: Every change logged with actor_type

💾 DATABASE
├─ delivery_orders - Main table with 17 status values
├─ order_status_history - Complete audit trail
├─ 11 timestamps - Covers all major events
├─ actor_type - Tracks WHO made change (user/courier/system)
└─ cancellation_reason - Documents WHY order ended
```

---

## State Diagram (ASCII)

```
                          ┌─────────────────────┐
                          │      DRAFT          │
                          │  Order Created      │
                          └──────────┬──────────┘
                                     │
                                     │ confirm() 
                                     │
                          ┌──────────▼──────────┐
                          │ AWAITING_COURIER    │
                          │ No Courier Yet      │
                          └──────────┬──────────┘
                                 ┌───┴───┐
                    acceptOrder()│       │expires()
                                 │       │
                          ┌──────▼─────┐│
                          │  ACCEPTED   ││
                          │Courier Busy ││
                          └──────┬──────┘│
                                 │       │
                                 │   EXPIRED
                    arrivingAtPickup()  (terminal)
                                 │
                          ┌──────▼─────────┐
                          │ARRIVING_AT_PICKUP
                          │On the way       
                          └──────┬──────────┘
                                 │
                            atPickup()
                                 │
                          ┌──────▼──────────┐
                          │   AT_PICKUP     │
                          │  Collecting      
                          └──────┬──────────┘
                                 │
                          pickupOrder()
                                 │
                          ┌──────▼──────────┐
                          │   PICKED_UP     │
                          │Item Collected    
                          └──────┬──────────┘
                                 │
                             inTransit()
                                 │
                          ┌──────▼──────────┐
                          │   IN_TRANSIT    │
                          │Traveling        
                          └──────┬──────────┘
                                 │
                      arrivingAtDropoff()
                                 │
                          ┌──────▼─────────────┐
                          │ARRIVING_AT_DROPOFF │
                          │Approaching         
                          └──────┬─────────────┘
                                 │
                            atDropoff()
                                 │
                          ┌──────▼──────────┐
                       ┌──┤  AT_DROPOFF    │
                       │  │Final Attempt    
                       │  └──────────────────┘
                       │
                ┌──────┴──────┐
           deliverOrder() │  deliveryFailed()
                │          │
                ▼          ▼
           DELIVERED    DELIVERY_FAILED  
         (TERMINAL)          │
                             │
                          returned()
                             │
                             ▼
                          RETURNED
                        (TERMINAL)

TERMINAL STATES (═════):
├─ DELIVERED ✓
├─ RETURNED ✗
├─ CANCELLED_BY_USER ✗
├─ CANCELLED_BY_COURIER ✗
├─ CANCELLED_BY_SYSTEM ✗
└─ EXPIRED ✗
```

---

**Reference:** Use this document when coding, testing, or explaining the system to stakeholders.

