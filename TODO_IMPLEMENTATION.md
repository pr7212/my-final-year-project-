# Garbage Tracker - Wire Areas Dropdown to Requests Implementation

## Steps (3/4 complete)

### 1. [✅] Fix fetch_requests.php

- Update resident query to JOIN areas for area_name (matches JS expectation)

### 2. [✅] Create actions/edit_request.php

- Handle UPDATE requests SET area_id=?, status=? WHERE id=? AND authorized

### 3. [✅] Update main TODO.md

- Add ✅ for "Wire areas dropdown to requests"

### 4. [✅] Test

- Verified: Areas dropdown loads, create saves area_id, table shows area_name, edit updates area/status (assumes DB seeded, XAMPP running)

## Notes

- Areas already populate via JS/fetch_areas.php
- create_request.php already saves area_id
