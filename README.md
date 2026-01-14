# PCM Solutions Documentation/Documentação
[EN English Documentation](./en) | [PT Documentação em Português](./pt)

Welcome to the **official documentation repository** of our organization
Here you’ll find all the information you need about our projects, processes, and best practices

Bem-vindo ao **repositório oficial de documentação** da nossa organização
Aqui encontrará toda a informação necessária sobre os nossos projetos, processos e melhores práticas

### Purpose
This repo serves as the **central knowledge base** for our team and collaborators
It includes technical guides, architecture decisions, API references, onboarding material, and operational documentation

### Propósito
Este repositório serve como a **base de conhecimento central** para a nossa equipa e colaboradores
Inclui guias técnicos, decisões de arquitetura, referências de API, material de onboarding e documentação operacional


### Languages
- **English** is our **primary source of truth**, since we code and communicate in English for technical matters
- **Portuguese** documentation is provided as a **translation** to support our current team and make onboarding easier
If there is any discrepancy, the **most recently updated** version takes priority, in case of a **tie**, the **English version prevails**.

### Línguas
- **Inglês** é a nossa **fonte principal de verdade**, uma vez que programamos e comunicamos em inglês para assuntos técnicos
- A documentação em **Português** é fornecida como **tradução** para apoiar a equipa atual e facilitar o onboarding
Se houver alguma discrepância, a **última versão atualizada** tem prioridade, sob **empate**, a **versão inglesa prevalece**.

---
## Structure / Estrutura
### Common (`/.github`)
- [ISSUE_TEMPLATE/docs_update.yml](./.github/ISSUE_TEMPLATE/docs_update.yml) - Template when adding or updating documentation
- [ISSUE_TEMPLATE/issue_report.yml](./.github/ISSUE_TEMPLATE/issue_report.yml) - Template for reporting errors or making suggestions
- [workflows/translation_check.yml](./.github/workflows/translation_check.yml) - Translation sync check when committing changes

### Comum (`/.github`)
- [ISSUE_TEMPLATE/docs_update.yml](./.github/ISSUE_TEMPLATE/docs_update.yml) - Modelo para adicionar ou atualizar documentação
- [ISSUE_TEMPLATE/issue_report.yml](./.github/ISSUE_TEMPLATE/issue_report.yml) - Modelo para reportar erros ou fazer sugestões
- [workflows/translation_check.yml](./.github/workflows/translation_check.yml) - Verificação de sincronização de tradução ao submeter alterações


### English (`/en`)
- [architecture.md](./en/architecture.md) - System architecture overview
- [getting_started.md](./en/getting_started.md) - Quick start guide for new users
- [api/](./en/api) - General API overview
- [daily_ops/](./en/daily_ops) - Daily operational processes
- [guides/](./en/guides)
    - [best_practices.md](./en/guides/best_practices.md) - Recommended development practices
    - [deployment.md](./en/guides/deployment.md) - Deployment workflows and strategies
- [projects/](./en/projects) - Project-specific documentation
- [resources/](./en/resources) - Shared resources and assets
- [scripts/](./en/scripts) - Useful scripts and automation
- [technical/](./en/technical) - Technical deep-dives, architecture, decisions
- [weekly_ops/](./en/weekly_ops) - Weekly operational notes

### Portuguese (`/pt`)
- [architecture.md](./pt/architecture.md) - Visão geral da arquitetura do sistema
- [getting_started.md](./pt/getting_started.md) - Guia rápido para novos utilizadores
- [api/](./pt/api) - Visão geral da API
- [daily_ops/](./pt/daily_ops) - Processos operacionais diários
- [guides/](./pt/guides)
    - [best_practices.md](./pt/guides/best_practices.md) - Boas práticas recomendadas
    - [deployment.md](./pt/guides/deployment.md) - Fluxos e estratégias de deployment
- [projects/](./pt/projects) - Documentação específica de projetos
- [resources/](./pt/resources) - Recursos e ativos partilhados
- [scripts/](./pt/scripts) - Scripts úteis e automações
- [technical/](./pt/technical) - Análises técnicas, arquitetura, decisões
- [weekly_ops/](./pt/weekly_ops) - Notas operacionais semanais

---
## Translation Status / Estado das Transações
| Location               | Type   | Status |
|------------------------|--------|--------|
| /                      | Files  |        |
| - architecture.md      | File   | Synced |
| - getting_started.md   | File   | Synced |
| /api                   | Folder | Synced |
| /daily_ops             | Folder | Synced |
| /guides                | Folder | Synced |
| - best_practices.md    | File   | Synced |
| - deployment.md        | File   | Synced |
| /projects              | Folder | Synced |
| /resources             | Folder | Synced |
| /scripts               | Folder | Synced |
| /technical             | Folder | Synced |
| /weekly_ops            | Folder | Synced |

### Legend
- **Synced** – translation is up-to-date in relation with the others
- **Outdated** – translation exists but needs updating. Example: if the Portuguese file was changed, mark *Edited - Lines Added: / Lines changed: / Lines removed:* in the portuguese file, if the english file wasn't updated with that change yet. Mark the table with "Outdated English"
- **Missing** – no translation available yet

### Legenda
**Sincronizado** – a tradução está atualizada em relação ao inglês
**Desatualizado lang** – a tradução existe, mas precisa de ser atualiada. Ex.: Alteraram o ficheiro português, devem escrever *Editado - Linhas Adicionadas: / Linhas alteradas: / Linhas removidas* no ficheiro em portugues, se ainda não fizeram a correspondente alteração no inglês. E deixar marcado "Desatualizado inglês"
**Ausente** – ainda não existe tradução disponível
Na tabela, **devem usar os termos em inglês!** 

---
## Rules
Name the folders using snake_case (underscores instead of spaces and all lowercase) and write the files in a natural way (“API Integration”), always in english
The sufix "raw" means there is something to add or improve until the doc is considered totally usable. You might write or find "IMPROVEMENT:" to know for what is missing

## Regras
Nomear as pastas, usando Snake Case (underscore em espaços e tudo minusculo) e escrever de forma natural ("Integração API") os ficheiros, sempre em inglês
O sufixo “raw” significa que ainda há algo a acrescentar ou melhorar até que o documento seja considerado totalmente utilizável. Deves de escrever ou encontrar "MELHORIA: para saber/indicar o que está em falta no documento

---
## Contributing
- When updating documentation, **always update the English version first**
- Then, update the Portuguese version if it exists
- Update the **Translation Status table** above to reflect the current state. Later we will add a script that allows for the status to be told in the beginning and then the table edits alone. For that reason, you must respect the legend above, accordingly to the file and respective language you are working on and write it on the first lines

## Contribuição
- Ao atualizar a documentação, **atualize sempre primeiro a versão em inglês**
- Em seguida, atualize a versão em português, caso exista
- Atualize a **Tabela de Estado de Tradução** acima para refletir o estado atual. Mais tarde, vamos adicionar um script que lê o status apartir da tabela acima, pelo que é crucial escrever na primeira linha do ficheiro de acordo com a legenda acima
