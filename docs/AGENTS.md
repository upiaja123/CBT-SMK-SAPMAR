# SAPTA CBT — AI AGENT MASTER INSTRUCTIONS

## 0. Mission

You are the primary software engineering agent responsible for building **SAPTA CBT**, a production-oriented, web-based Computer Based Test and School Examination Management Platform for SMK Sapta Marga.

Your job is not to create a demo or a collection of CRUD pages. Build a reliable end-to-end examination platform that can be used by Admin, Kurikulum, Guru, Proktor/Pengawas, and Siswa, with mobile-first UX, real-time operational monitoring, secure exam execution, autosave/resume, grading, analytics, auditability, and production deployment readiness.

This file is the persistent execution contract for the project. Treat it as a higher-priority project instruction than convenience, shortcuts, or assumptions.

---

## 1. Product Vision

SAPTA CBT must replace fragmented/manual exam workflows with one integrated platform:

Preparation → Question Authoring → Review/Approval → Exam Configuration → Scheduling → Secure Exam Session → Autosave/Recovery → Monitoring → Grading → Analytics → Reporting → Archive.

The core business problem being solved is that exam preparation and question entry should not be a bottleneck on one curriculum operator. Teachers must be able to create and manage their own questions, while administrators and curriculum staff retain control over exam governance.

---

## 2. Non-Negotiable Product Principles

1. **Reliability before visual novelty.** A lost answer is a critical defect.
2. **Server is authoritative for exam rules.** The browser may display state but must not be trusted for timing, authorization, scoring, or eligibility.
3. **One authoritative database in the initial architecture.** Do not invent multi-database synchronization unless explicitly required later.
4. **Real-time where it has operational value.** Use WebSocket/events for live monitoring, participant status, alerts, and operator controls. Do not force WebSockets onto ordinary CRUD.
5. **Role-based access control must be enforced server-side.** Never rely only on hidden buttons or frontend checks.
6. **Every sensitive administrative action must be auditable.** Full admin control does not mean invisible control.
7. **Question banks and exams are separate concepts.** Questions must be reusable without rewriting completed exams.
8. **Exam attempts must be immutable in the ways that protect historical truth.** Completed exams must not silently change because a teacher later edits a question.
9. **Mobile-first means the interface is designed for phones first, not desktop pages merely squeezed smaller.**
10. **Do not claim 100% anti-cheating.** Build exam-integrity signals and controls, not impossible guarantees.
11. **Do not add complexity without value.** Avoid premature microservices, Kubernetes, AI proctoring, blockchain, or distributed database synchronization.
12. **Do not silently drop requirements.** If a requirement is technically impossible or conflicts with another requirement, stop, explain the conflict, and propose the safest alternative.

---

## 3. Primary Roles

### Super Admin / Admin Utama

Full system control, including all roles, users, school structure, subjects, question banks, exams, reports, settings, security, and emergency controls.

Full control MUST still produce audit records for sensitive actions.

Academic examination governance: exam planning, scheduling, review/approval workflows, monitoring oversight, and reporting according to assigned permissions.

### Kurikulum
Full system control, including all roles, users, school structure, subjects, question banks, exams, reports, settings, security, and emergency controls.

Full control MUST still produce audit records for sensitive actions.

Academic examination governance: exam planning, scheduling, review/approval workflows, monitoring oversight, and reporting according to assigned permissions.

### Guru

Own/manage assigned subject question banks; create/edit/import/archive questions; assemble exams as authorized; grade essay questions; inspect results and item analysis within scope.

### Proktor / Pengawas

Operational exam control only. Can monitor active participants, investigate integrity signals, reset sessions where authorized, lock/unlock, extend time where authorized, and handle operational incidents. Must not edit question content or system configuration unless explicitly granted by permission.

### Siswa

Mobile-first participant role. Login → see eligible exams → token/access validation → instructions → secure exam session → answer → autosave → submit/auto-submit → result according to exam review policy.

---

## 4. Permission Model

Use roles plus explicit permissions. Do not hard-code authorization around role names everywhere.

Examples of permissions:

- users.view / create / update / delete
- roles.manage
- classes.manage
- subjects.manage
- question_banks.view / create / update / archive
- questions.create / update / delete / publish / review
- exams.create / update / schedule / publish / archive
- exams.monitor
- exams.lock / unlock
- exams.extend_time
- attempts.reset
- results.view
- results.export
- essay.grade
- analytics.view
- audit_logs.view
- system.settings.manage

All authorization decisions must be enforced on the backend.

---

## 5. Core Functional Modules

### A. Authentication & Identity

- Login/logout
- Secure password hashing
- Session security
- Account status
- Password reset/change flows as appropriate
- Role/permission enforcement
- Optional profile data appropriate to school use

Do not expose passwords or secrets in logs, URLs, responses, or source control.

### B. School Master Data

At minimum:

- School profile
- Academic year
- Semester/term
- Major/program
- Class
- Subject
- Teacher
- Student
- User account

Support relationships between teachers, subjects, and classes.

### C. Question Bank

A question belongs to a reusable bank and can contain:

- subject
- grade
- class/major scope where needed
- topic
- competency/learning target
- cognitive level
- difficulty
- question type
- content
- media attachments
- author
- status
- version information
- usage/history metadata

Question lifecycle:

DRAFT → REVIEW → APPROVED/PUBLISHED → ARCHIVED

Do not destroy old versions that are required to reproduce a historical exam.

### D. Supported Question Types

Initial required types:

- Multiple choice
- Complex multiple choice / multiple answer
- True/false
- Matching
- Short answer / fill-in
- Essay / constructed response
- Listening/audio-based questions

Design the data model so additional interactive types can be added later without redesigning the entire database.

### E. Question Import / Export

Support bulk question import, especially Excel/CSV-style workflows.

Import must validate:

- required fields
- question type
- answer structure
- duplicates where applicable
- invalid references
- supported media/file types
- file size

Import must produce useful row-level error reporting rather than generic failure.

### F. Exam Builder

An exam must support:

- title and description
- subject
- target class/cohort
- schedule/open/close time
- duration
- token/access rules
- question selection from bank
- explicit or random selection
- random question order
- random answer option order where applicable
- sections
- scoring configuration
- review/reveal policy
- attempt policy
- late access policy
- exam state
- participant assignment
- optional listening/media configuration

### G. Exam Blueprint / Controlled Randomization

Avoid naive randomization only.

Allow future support for constraints such as:

- number of questions
- difficulty distribution
- topic distribution
- competency distribution
- question-type distribution

When a student starts an exam, create a participant-specific immutable question/order snapshot so later edits to the question bank cannot alter that attempt.

### H. Exam Lifecycle

Recommended exam states:

DRAFT → REVIEW → SCHEDULED → OPEN/ONGOING → ENDED → GRADED → ARCHIVED

Participant attempt states may include:

NOT_STARTED → IN_PROGRESS → SUBMITTED / AUTO_SUBMITTED → GRADING / GRADED → FINALIZED

State transitions must be validated server-side.

### I. Secure Exam Session

At exam start:

1. Authenticate participant.
2. Verify exam eligibility.
3. Validate token/access rules.
4. Verify schedule and allowed time window.
5. Prevent unauthorized duplicate active sessions.
6. Build/freeze participant question snapshot.
7. Establish attempt record.
8. Establish server-authoritative deadline.

### J. Server-Authoritative Timer

The server must determine the actual deadline.

Do not trust client JavaScript timers for enforcement.

Example concept:

start_at + allowed_duration = deadline

The frontend only renders the remaining time. The backend rejects answers/submits that violate the allowed deadline unless an authorized extension has been recorded.

### K. Autosave & Recovery

Answer persistence is a critical reliability feature.

Required behavior:

- save answers efficiently and frequently enough for low data loss
- show saving/saved/error state to the student
- tolerate temporary network failure
- queue unsent changes client-side where safe
- synchronize when connectivity returns
- avoid duplicate answer writes
- preserve the latest valid answer according to deterministic conflict rules
- allow controlled resume after reconnect/reload/device incident

Do not send a server request for every keystroke of a text answer.

### L. Submission

Manual submission:

- confirm action
- validate attempt state
- persist final answers
- finalize attempt
- trigger grading where applicable

Automatic submission:

- triggered when deadline expires
- must be server-enforced
- must preserve last successfully saved answer state

Prevent double submission and race conditions.

### M. Grading

Automatic grading for objective types where deterministic rules are available.

Manual grading for essay/constructed response.

Store:

- awarded score
- maximum score
- grader
- grading timestamp
- feedback if used
- grading status

Use states such as AUTO_GRADED, PARTIALLY_GRADED, FINALIZED as appropriate.

### N. Monitoring / Real-Time Operations

Real-time is required for operational state such as:

- participant online/offline/connectivity state
- current progress
- active attempt state
- focus/tab/fullscreen integrity signals
- proctor alerts
- lock/unlock operations
- time extensions
- session resets
- critical exam-wide announcements

Use a proper event/broadcast layer. Do not poll aggressively when an event-driven mechanism is appropriate.

### O. Exam Integrity / Anti-Cheat

Required initial signals/controls:

- randomized questions
- randomized options where safe
- one authorized active attempt/session
- server-authoritative timing
- token/access controls
- focus loss detection
- tab/window visibility change logging
- fullscreen state monitoring where supported
- shortcut/context-menu deterrents as supplemental controls
- integrity event log
- human proctor review

The browser is not a trusted security boundary. Never rely on UI restrictions alone.

Do not automatically label a participant “cheating” based on a single browser event. Store signals for review.

### P. Camera / Audio Proctoring

Do NOT make continuous camera/audio recording the MVP.

Reserve architecture hooks for future proctoring, but require explicit privacy, retention, access, and security decisions before implementation.

### Q. Analytics

At minimum support:

- participant scores
- class summaries
- subject/exam summaries
- completion rates
- correct/incorrect/unanswered counts
- question-level performance
- difficulty indicators
- option/distractor distribution for objective questions
- exportable reports

Design analytics so teachers can detect potentially ambiguous or poor questions, not merely see final scores.

### R. Audit Log

Sensitive actions must create audit entries including, as appropriate:

- actor
- action
- target/entity
- timestamp
- before/after summary where safe
- reason/comment for emergency/manual overrides when required
- request/session/device context where appropriate
- outcome

Audit data must itself be protected from ordinary editing/deletion.

### S. Reporting / Export

Support:

- student result
- exam result recap
- class recap
- question/item analysis
- essay grading report
- monitoring/integrity summary
- Excel export
- PDF export where practical

Exports must obey permissions and avoid exposing unrelated student data.

### T. Backup & Recovery

Production must have:

- database backup strategy
- file/media backup strategy
- retention policy
- recovery procedure
- documented restore test

A backup that has never been restore-tested is not considered verified.

---

## 6. Database Architecture

Use a relational database suitable for transactional exam workloads. Prefer clear normalized relationships with strategic indexes.

The conceptual model should include entities in these families:

### Identity / School

- users
- roles
- permissions
- role_user / user_roles
- role_permission / permission_roles as appropriate
- students
- teachers
- academic_years
- semesters
- majors/programs
- classes
- subjects
- teacher_subject assignments

### Question Bank

- question_banks
- questions
- question_versions
- question_options
- question_media
- question_tags / topics / competencies as needed

### Exams

- exams
- exam_settings
- exam_sections
- exam_questions
- exam_participants
- participant_question_snapshots or equivalent immutable attempt-question records

### Attempts / Answers

- attempts / exam_sessions
- answers
- answer_events if needed for audit/recovery architecture
- grading records
- results

### Monitoring / Security

- proctoring_events / integrity_events
- participant_presence / connection state if persisted
- audit_logs
- notifications

### Media / Files

Use secure file metadata and storage references rather than storing unsafe raw filenames blindly.

Important relational rules:

- completed attempts must remain reproducible
- historical exam data must not be silently rewritten by current question edits
- foreign keys and cascading behavior must be deliberate
- use indexes for high-frequency lookups such as exam_id, participant_id, user_id, question_id, status, and timestamps
- use transactions for critical exam operations
- avoid denormalization unless there is a measured performance reason

Do not create redundant `login` tables when authentication can be represented cleanly through the main users/auth model.

Do not store plaintext passwords.

---

## 7. Recommended Laravel-Oriented Architecture

Prefer a maintainable Laravel-first monolith for the initial version rather than premature service decomposition.

Recommended conceptual stack:

- Laravel
- Blade/Livewire or another Laravel-native interactive frontend approach
- Tailwind CSS or equivalent design system
- relational SQL database (MySQL/MariaDB or PostgreSQL according to the final environment)
- Redis for cache/queues/realtime support where appropriate
- Laravel Reverb or equivalent WebSocket layer for realtime events
- queue workers for heavy background work

Keep business logic out of controllers where complexity warrants services/actions/domain classes.

Recommended layer separation:

HTTP/UI → Controllers/Livewire → Application Services/Actions → Domain Rules → Repositories/ORM → Database

Realtime:

Domain event → Event/Listener → Broadcast → Connected clients

Heavy jobs:

Request → Queue Job → Worker → Persist result/status → Notify user

Do not over-engineer the project into microservices without a clear operational requirement.

---

## 8. UI/UX Design System

The interface must be:

- attractive
- modern
- energetic
- interactive
- professional
- not visually boring
- easy to scan
- mobile-first
- responsive across phone/tablet/laptop/desktop
- accessible

### Brand Direction

Use the official SMK Sapta Marga logo as the source for the visual palette when the logo asset is available in the project.

Do not invent random brand colors that conflict with the real logo.

Extract or approximate a coherent palette from the official logo and define design tokens for:

- primary
- secondary
- accent
- success
- warning
- danger
- info
- neutral/background/surface

The UI should feel like one product, not a collection of unrelated templates.

### Visual Style

Use:

- strong but tasteful brand colors
- clear cards and grouped information
- responsive tables with mobile alternatives
- meaningful icons
- micro-interactions
- loading/skeleton states
- success/error feedback
- empty states
- confirmation dialogs for dangerous actions
- clear status badges
- progress indicators
- obvious primary actions

Avoid:

- excessive gradients
- decorative animations that distract during exams
- tiny buttons
- dense admin screens on mobile
- excessive colors with no semantic meaning
- unnecessary modal stacking

### Role-Focused UI

Admin:

- system overview
- operational alerts
- master data
- exam governance
- reports
- security/audit

Guru:

- my question banks
- my exams
- grading queue
- results
- item analysis

Proctor:

- active exam overview
- participant grid/list
- integrity alerts
- emergency actions

Student:

- today's/upcoming exams
- simple instructions
- prominent start/continue action
- clean exam screen

### Exam Interface

The exam screen is a special mode and must minimize distractions.

Must include:

- exam title
- remaining time
- current question number
- question content
- options/response field
- previous/next navigation
- question navigator
- answered/unanswered/ragu status
- save state
- submit control
- clear error/reconnect state

Never make the student hunt through the interface to find the next question.

### Mobile

Student exam flow must work comfortably on small screens. Touch targets must be large enough, text must be legible, and controls must not overlap content.

Do not use color as the only status indicator.

---

## 9. File Upload Security

For images, audio, Excel, PDF, or other uploads:

- validate file extension
- validate actual file type/content
- enforce size limits
- generate safe server-side filenames
- prevent path traversal
- store uploads outside a directly executable webroot when practical
- authorize who can upload/access files
- prevent untrusted files from being executed as scripts
- record metadata

Never trust `Content-Type` alone.

---

## 10. Security Baseline

At minimum:

- HTTPS in production
- secure authentication
- password hashing
- server-side authorization
- CSRF protection where applicable
- validation and sanitization
- output escaping
- rate limiting/throttling for login and sensitive endpoints
- secure session cookies
- secure session lifecycle
- audit logging
- secure file uploads
- database backups
- secret management through environment configuration
- no secrets in Git

For every sensitive mutation, verify authorization on the server immediately before the mutation.

Treat student data, exam content, answers, results, and proctoring data as sensitive.

---

## 11. Realtime Engineering Rules

Do not make every action realtime.

### Use realtime for:

- participant presence/state
- live progress
- proctor alerts
- exam-wide operational changes
- lock/unlock
- time extension
- urgent notifications

### Use normal request/response for:

- CRUD
- settings
- question editing
- reporting queries
- archives

Realtime events must be idempotent where possible and must not become the source of truth. The database remains authoritative.

If a WebSocket message is missed, the client must be able to recover current state through a normal API refresh/sync.

---

## 12. Performance Requirements

Initial target: roughly up to 100 concurrent student participants, but design should leave room for growth.

Performance priorities:

1. Fast login/session establishment.
2. Fast exam start.
3. Efficient question delivery.
4. Efficient answer persistence.
5. Stable simultaneous submit behavior.
6. Efficient monitoring queries.
7. Avoid N+1 database queries.
8. Use indexes intentionally.
9. Cache data that is safe to cache.
10. Move heavy work to queue workers.

Do not benchmark only one user. Test realistic concurrency.

---

## 13. Testing Strategy

Every major module must include appropriate tests.

### Unit Tests

Test business rules such as:

- eligibility
- scoring
- state transitions
- time extension rules
- randomization constraints
- permissions

### Feature / Integration Tests

Test flows such as:

Teacher creates question → publishes → creates exam → assigns participants → student starts → answers → submits → grading → result.

### Security Tests

Attempt:

- unauthorized admin route access
- cross-user data access
- cross-exam access
- ID manipulation
- duplicate submission
- invalid token
- expired exam access
- privilege escalation
- unsafe upload

### Failure/Recovery Tests

Simulate:

- refresh during exam
- temporary network loss
- reconnect
- duplicate answer submission
- duplicate submit
- expired session
- time expiry
- browser back/forward
- device sleep/wake

### Load Tests

At minimum test:

- 10 users
- 50 users
- 100 users

Include login bursts, question retrieval, autosave, navigation, and simultaneous submission.

### Acceptance Criteria

A feature is not "done" because the page renders. It is done only when:

- business rule works
- authorization works
- validation works
- persistence works
- failure behavior is acceptable
- tests exist
- UI is usable on target devices
- no known critical defect remains

---

## 14. Deployment Philosophy

Development environment:

- local Laravel environment
- version control

Server learning/testing environment:

- Ubuntu Server VM may be used as a deployment laboratory
- Nginx
- PHP
- SQL database
- Redis/queues/realtime where required

Production:

- managed VPS/cloud environment
- HTTPS/domain
- production configuration
- database backup
- monitoring/logging
- queue workers
- restart/recovery procedures

Do not treat a laptop VirtualBox instance as the final production infrastructure for an important school exam unless explicitly approved for a controlled test.

---

## 15. Development Workflow — Mandatory

Before editing code:

1. Inspect the existing repository.
2. Identify framework/version and installed dependencies.
3. Identify current migrations/models/routes/components.
4. Read relevant existing code before replacing it.
5. Preserve working features unless a change is intentionally required.
6. Plan the smallest coherent implementation.

When implementing a feature:

1. Update data model/migration if needed.
2. Implement business rules.
3. Implement authorization.
4. Implement validation.
5. Implement UI/API.
6. Implement tests.
7. Run formatter/static checks/tests.
8. Manually verify critical UI flow.
9. Report exactly what changed.

Never declare a feature complete without verification.

---

## 16. Agent Decision Rules

When requirements are ambiguous:

- Prefer the safest interpretation that preserves data integrity.
- Do not invent hidden business rules.
- Record assumptions.
- If an assumption can materially change architecture or data, ask before committing.

When requirements conflict:

- Identify the conflict explicitly.
- Prioritize data integrity, security, exam reliability, and user safety.
- Propose an alternative rather than silently choosing.

When a feature is complex:

- Build the simplest architecture that solves the real requirement.
- Keep extension points for future improvements.
- Do not implement speculative infrastructure.

When external documentation/current technical behavior matters:

- Verify against current official documentation before relying on potentially changed framework/provider behavior.

---

## 17. Explicit Rejections / Do-Not-Do List

Do NOT:

- build the system as a Google Forms clone
- force curriculum staff to re-enter teacher questions
- keep question banks tightly coupled to one exam
- trust browser timers for final exam enforcement
- trust frontend authorization
- store plaintext passwords
- let a later question edit rewrite a historical submitted exam
- use uncontrolled randomization that breaks content balance
- make camera/audio recording the MVP
- promise perfect anti-cheat
- introduce microservices only because they sound advanced
- introduce distributed database synchronization without a demonstrated need
- use polling for every realtime event
- send every keystroke to the server
- hide critical errors from the user
- silently swallow failed autosaves
- perform dangerous admin actions without audit logging
- expose sensitive student/exam data through unauthorized endpoints

---

## 18. Definition of Done — System Level

The SAPTA CBT system is not production-ready until the following are true:

- [ ] all required roles and permissions work
- [ ] teacher question authoring works
- [ ] question import works with useful validation
- [ ] question versioning/archive rules work
- [ ] exam scheduling works
- [ ] tokens/access rules work
- [ ] question randomization works
- [ ] participant snapshot works
- [ ] server-authoritative timer works
- [ ] autosave works
- [ ] recovery/resume works
- [ ] duplicate submission is prevented
- [ ] auto grading works
- [ ] essay grading works
- [ ] results are correct and reproducible
- [ ] realtime monitoring works
- [ ] integrity events are logged
- [ ] audit logging works
- [ ] exports work
- [ ] secure uploads work
- [ ] database backup/restore is documented and tested
- [ ] responsive mobile UI works
- [ ] critical accessibility requirements are respected
- [ ] automated tests pass
- [ ] security tests pass
- [ ] load tests have been executed for the target concurrency
- [ ] production deployment is documented

---

## 19. Implementation Order

Do not build randomly. Follow this sequence unless an explicit dependency requires a change.

### Phase 1 — Foundation

- Laravel project inspection/setup
- authentication
- users
- roles
- permissions
- school master data
- base layout/design system

### Phase 2 — Question Bank

- question bank
- question model
- question options
- media
- question metadata
- version/status lifecycle
- import/export

### Phase 3 — Exam Management

- exam
- settings
- sections
- exam question selection
- participants
- scheduling
- token/access policy
- controlled randomization

### Phase 4 — Exam Engine

- attempt/session
- participant snapshot
- timer
- answer persistence
- autosave
- resume/recovery
- submission
- auto-submit

### Phase 5 — Grading & Results

- objective grading
- essay grading
- finalization
- results
- teacher views
- student result policy

### Phase 6 — Realtime Monitoring

- presence
- progress
- integrity events
- live alerts
- lock/unlock
- time extension
- reset session

### Phase 7 — Analytics & Reporting

- class results
- item analysis
- distributions
- exports
- dashboards

### Phase 8 — Security & Hardening

- audit log
- rate limiting
- secure upload
- authorization review
- session hardening
- backup/recovery

### Phase 9 — Performance & Production

- caching
- queue workers
- realtime tuning
- database/index optimization
- load tests
- deployment
- HTTPS/domain
- monitoring

### Phase 10 — Advanced Features

Only after the core platform is stable:

- advanced proctoring
- camera/audio workflows
- richer AI-assisted question authoring
- additional question interaction types

---

## 20. Communication Style While Working

When reporting progress:

- state what was implemented
- state what was tested
- state any known limitation
- state any decision/assumption that affects future work
- do not exaggerate completion

Use practical engineering language.

Never say "done" merely because code was generated.

---

## 21. Final Instruction

Build SAPTA CBT as a serious school examination platform.

The goal is not maximum feature count.
The goal is a system that is:

**reliable + secure + realtime where useful + mobile-first + beautiful + easy to use + maintainable + testable + deployable.**

When choosing between two technically possible approaches, prefer the one that is easier to verify, easier to recover, safer for student/exam data, and simpler to maintain while still meeting the requirement.
