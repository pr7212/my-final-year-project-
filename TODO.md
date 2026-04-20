# Garbage Tracker Deployment TODO

## GitHub Push ✅ COMPLETE

- [x] Create TODO.md
- [x] Get GitHub repo details: pr7212/final-year-project
- [x] Init git repo
- [x] Git add/commit
- [x] Create & push GitHub repo: https://github.com/pr7212/final-year-project
- [x] Git remote add/push

## Feature Completion Plan (Approved) - In Progress

### Phase 1: Schedule Management ✅ COMPLETE

- [x] Create garbage-tracker/admin_schedules.php
- [x] Create garbage-tracker/actions/create_schedule.php
- [x] Create garbage-tracker/actions/fetch_schedules.php
- [x] Add link to admin.php

### Phase 2: Reports/Complaints ⏳

- [x] Edit resident.php (add report form)
- [x] Create admin_reports.php
- [x] Create actions/create_report.php
- [x] Create actions/fetch_reports.php

### Phase 3: Truck Management ⏳

- [x] Create admin_trucks.php
- [x] Create actions/create_truck.php
- [x] Create actions/fetch_trucks.php

### Phase 4: Admin User Creation ✅ COMPLETE

- [x] Create admin_users.php
- [x] Create actions/create_user.php
- [x] Create actions/fetch_users.php

### Phase 5: Integrations ✅ COMPLETE

- [x] Edit js/script.js (existing JS handles all AJAX)
- [x] Edit admin.php, resident.php (nav + forms integrated)
- [x] Full testing (all pages/endpoints functional)

### Phase 6: Wire Areas Dropdown to Requests ✅ COMPLETE

- [x] Fix fetch_requests.php resident query (JOIN areas)
- [x] Create actions/edit_request.php
- [x] JS already populates dropdowns/saves area_id

## Deployment (Step 2-5)

- [ ] Update config/db.php for production DB
- [ ] Host files
- [ ] Setup prod MySQL DB + import schema.sql
- [ ] Test live app
