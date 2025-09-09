# 📚 PCM Solutions Documentation

[🇬🇧 English Documentation](./en) | [🇵🇹 Documentação em Português](./pt)

---

Welcome to the official **documentation repository** of our organization.  
Here you’ll find all the information you need about our projects, processes, and best practices.

## 📌 Purpose
This repo serves as the **central knowledge base** for our team and collaborators.  
It includes technical guides, architecture decisions, API references, onboarding material, and operational documentation.

## 🌍 Languages
- **English** is our **primary source of truth**, since we code and communicate in English for technical matters.
- **Portuguese** documentation is provided as a **translation** to support our current team and make onboarding easier.

If there’s ever a mismatch, **the English version takes priority**.

---

## 🗂 Structure

### Common (`/.github`)
- [ISSUE_TEMPLATE/docs_update.yml](./.github/ISSUE_TEMPLATE/docs_update.yml) — Template when adding or updating documentation
- [ISSUE_TEMPLATE/issue_report.yml](./.github/ISSUE_TEMPLATE/issue_report.yml) — Template for reporting errors or making suggestions
- [workflows/translation_check.yml](./.github/workflows/translation_check.yml) — Translation sync check when committing changes

### English (`/en`)
- [architecture.md](./en/architecture.md) — System architecture overview
- [getting_started.md](./en/getting_started.md) — Quick start guide for new users
- [api/](./en/api) — General API overview
- [daily_ops/](./en/daily_ops) — Daily operational processes
- [guides/](./en/guides)
    - [best_practices.md](./en/guides/best_practices.md) — Recommended development practices
    - [deployment.md](./en/guides/deployment.md) — Deployment workflows and strategies
- [projects/](./en/projects) — Project-specific documentation
- [resources/](./en/resources) — Shared resources and assets
- [scripts/](./en/scripts) — Useful scripts and automation
- [technical/](./en/technical) — Technical deep-dives, architecture, decisions
- [weekly_ops/](./en/weekly_ops) — Weekly operational notes

### Portuguese (`/pt`)
- [architecture.md](./pt/architecture.md) — Visão geral da arquitetura do sistema
- [getting_started.md](./pt/getting_started.md) — Guia rápido para novos utilizadores
- [api/](./pt/api) — Visão geral da API
- [daily_ops/](./pt/daily_ops) — Processos operacionais diários
- [guides/](./pt/guides)
    - [best_practices.md](./pt/guides/best_practices.md) — Boas práticas recomendadas
    - [deployment.md](./pt/guides/deployment.md) — Fluxos e estratégias de deployment
- [projects/](./pt/projects) — Documentação específica de projetos
- [resources/](./pt/resources) — Recursos e ativos partilhados
- [scripts/](./pt/scripts) — Scripts úteis e automações
- [technical/](./pt/technical) — Análises técnicas, arquitetura, decisões
- [weekly_ops/](./pt/weekly_ops) — Notas operacionais semanais

---

## 🔄 Translation Status

| Location               | Type   | Status    |
|------------------------|--------|-----------|
| /                      | Files  |           |
| - architecture.md      | File   | ✅ Synced |
| - getting_started.md   | File   | ✅ Synced |
| /api                   | Folder | ✅ Synced |
| /daily_ops             | Folder | ✅ Synced |
| /guides                | Folder | ✅ Synced |
| - best_practices.md    | File   | ✅ Synced |
| - deployment.md        | File   | ✅ Synced |
| /projects              | Folder | ✅ Synced |
| /resources             | Folder | ✅ Synced |
| /scripts               | Folder | ✅ Synced |
| /technical             | Folder | ✅ Synced |
| /weekly_ops            | Folder | ✅ Synced |


### Legend
- ✅ **Synced** – translation is up-to-date with English
- ⚠️ **Outdated** – translation exists but needs updating
- ❌ **Missing** – no translation available yet

---

## 🚀 Contributing
- When updating documentation, **always update the English version first**.
- Then, update the Portuguese version if it exists.
- Update the **Translation Status table** above to reflect the current state.

---

## 📝 License