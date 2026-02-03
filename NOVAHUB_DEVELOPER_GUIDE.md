# 🧩 Nova Hub Developer Guide v1.0.0

> *Every commit tells our story — build it with care and clarity.*

---

## 🎯 7. Naming Conventions

| Item | Convention | Example |
|------|------------|---------|
| **Branch** | `feature/<short-desc>` / `fix/<short-desc>` | `feature/add-student-attendance` |
| **Commit** | `type(scope): message` | `feat(api): add student registration` |
| **Controller** | `PascalCase + Controller` | `StudentController` |
| **Model** | `Singular PascalCase` | `Student` |
| **Migration** | `snake_case` | `create_students_table` |
| **DB Column** | `snake_case` | `student_id` |
| **Folder** | `kebab-case` | `student-reports/` |
| **Env Var** | `UPPER_SNAKE_CASE` | `APP_ENV=local` |

### Commit Types
- `feat` - New feature
- `fix` - Bug fix
- `chore` - Maintenance tasks
- `refactor` - Code restructuring
- `test` - Adding/updating tests
- `docs` - Documentation updates
- `style` - Code formatting (no logic change)
- `perf` - Performance improvements

---

## 🔄 8. Git Flow & Branch Rules

```
main          ← production branch
└── develop   ← integration branch
    ├── feature/<task>
    ├── fix/<bug>
    └── hotfix/<urgent>
```

### Workflow Rules
- ✅ Developers work in **feature branches**
- ✅ Push to the **testing branch** (for staging)
- ✅ Team Lead merges tested code to **production**
- ✅ Pull requests must pass **code review + tests**

---

## 🕐 9. Scrum Workflow

- **Sprint Duration:** 1 week
- **Daily Stand-up:** 15 min update
- **Board:** GitHub Projects or Notion Kanban
- **Columns:** Backlog → To Do → In Progress → Testing → Done
- **Retrospective:** End of each sprint

---

## 🧾 10. Code Review Checklist

- ✅ Code follows **SOLID + PSR-12**
- ✅ Proper **naming conventions**
- ✅ No **hard-coded secrets**
- ✅ API responses **standardized**
- ✅ Proper **validation / error handling**
- ✅ **Unit / Feature tests** included
- ✅ **Migration + Seeder** safe to run
- ✅ No **sensitive logs / debugs**

---

## 🔐 11. Environment & Security Policy

### Security Requirements
- ✅ Store secrets **only in .env**
- ✅ **Never commit** .env or keys
- ✅ Use **APP_KEY** and **Sanctum tokens** for auth
- ✅ **Backups:** DB daily, code weekly
- ✅ **Encrypt sensitive user data** (PII, payroll)
- ✅ Enable **2FA** for admin accounts
- ✅ Secure passwords via **bcrypt** or **Argon2**

---

## 🧪 12. Testing Guidelines

- **Framework:** Pest
- **Coverage Target:** ≥ 70%
- **Naming:** `<FeatureName>Test.php`

### Write Tests For
- Controllers
- Repositories
- Services

### Example Test

```php
it('stores new student', function () {
    $response = post('/api/students', StudentFactory::make()->toArray());
    $response->assertStatus(201);
});
```

---

## ☁️ 13. Deployment Workflow

### 1. Local → Testing Server
- Dev pushes → **testing branch**
- Auto deploy to **staging**

### 2. Testing → Production
- PM reviews and merges → **production**
- Tag release: `v1.1.0`
- Run:
  ```bash
  php artisan migrate --force
  php artisan optimize
  ```

---

## 🤖 14. AI Collaboration Policy

Nova Hub integrates AI in development, but **humans make final decisions**.

### Use AI For
- ✅ Code scaffolding & boilerplates
- ✅ Technical docs generation
- ✅ Code review suggestions
- ✅ Sprint planning summaries
- ✅ Natural-language queries to internal RAG systems

### AI Rules
- ⚠️ **Always review AI output** before commit
- ⚠️ **Never feed client data** into public LLMs
- ⚠️ Use **internal local LLM** for sensitive content (later phase)

---

## 🧭 15. Future Enhancements

- [ ] Automate CI/CD pipeline with GitHub Actions
- [ ] Introduce Docker for containerized local dev
- [ ] Add static analysis (PHPStan / Larastan)
- [ ] Build internal AI assistant for code review

---

## 📖 16. Appendix

### Useful CLI Commands

```bash
php artisan optimize
php artisan migrate:fresh --seed
npm run build
./vendor/bin/pest
```

### Recommended Tools

- **IDE:** VS Code + Laravel IDE Helper
- **Database:** TablePlus / DB Beaver
- **API Testing:** Postman / Insomnia
- **Design:** Figma (for UI/UX review)

---

**End of Nova Hub Developer Guide v1.0.0**

*Every commit tells our story — build it with care and clarity.*
