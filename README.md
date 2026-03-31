# PlaylistApp — Teste de Contratação Full Stack

Aplicação de gerenciamento de playlists estilo Spotify.

**Stack:** AngularJS 1.x + Bootstrap 3 · PHP 5.6 + Yii 1.1 · MySQL 5.7

---

## Pré-requisito único: Docker Desktop

Tudo roda via Docker. Você **não precisa** instalar PHP, MySQL ou servidor web na sua máquina.

### Instalar o Docker Desktop

**Windows:**
1. Acesse https://www.docker.com/products/docker-desktop/ e baixe o instalador
2. Execute o instalador (pode pedir para reiniciar o computador)
3. Após reiniciar, abra o **Docker Desktop** pela área de trabalho ou menu iniciar
4. Aguarde o ícone da baleia na barra de tarefas ficar estável (para de animar)

**macOS:**
1. Acesse https://www.docker.com/products/docker-desktop/ e baixe para Mac
2. Arraste o Docker.app para a pasta Applications e abra
3. Aguarde o ícone da baleia no menu bar estabilizar

**Linux (Ubuntu/Debian):**
```bash
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER
# Faça logout e login novamente para aplicar o grupo
```

> **Atenção Windows:** se aparecer erro sobre WSL 2 ao abrir o Docker Desktop, abra o PowerShell como Administrador e execute:
> ```powershell
> wsl --update
> wsl --set-default-version 2
> ```
> Depois reabra o Docker Desktop.

---

## Opção A — Setup automático (recomendado)

Com o Docker Desktop aberto e rodando, execute no terminal **dentro da pasta do projeto**:

```bash
python setup.py
```

O script vai fazer tudo automaticamente:
1. Verificar se o Docker está instalado e rodando
2. Checar se as portas 3001, 8080 e 3306 estão livres
3. Buildar e subir os 3 containers (`docker compose up --build -d`)
4. Aguardar os serviços ficarem prontos
5. Confirmar que o login da API está funcionando
6. Abrir o browser em `http://localhost:3001`

> Na **primeira execução** demora 3–8 minutos enquanto baixa imagens e instala dependências.
> Nas próximas, menos de 30 segundos.

---

## Opção B — Passo a passo manual

### 1. Clonar o repositório

```bash
git clone https://github.com/kleberbernardo/teste_lx.git
cd teste_lx
```

### 2. Verificar que o Docker Desktop está rodando

```bash
docker info
```

Se retornar informações sobre o daemon, está OK. Se der erro de conexão, abra o Docker Desktop e aguarde estabilizar.

### 3. Buildar e subir os containers

```bash
docker compose up --build
```

Esse comando sobe 3 serviços:

| Container | Função | Porta |
|-----------|--------|-------|
| `playlist_api` | PHP 5.6 + Apache (API REST) | 8080 |
| `playlist_frontend` | Nginx servindo o AngularJS | 3001 |
| `playlist_db` | MySQL 5.7 | 3306 |

Na **primeira execução**, o container da API vai automaticamente:
- Baixar o Yii 1.1.29 do GitHub
- Instalar as dependências via Composer (`firebase/php-jwt`)
- Criar a pasta `runtime/` com permissões corretas

Aguarde aparecer no terminal:
```
playlist_api  | [entrypoint] Iniciando Apache...
playlist_db   | ready for connections
```

> Dica: para rodar em background (sem travar o terminal), use `docker compose up --build -d`

### 4. Importar o banco de dados

O banco é importado automaticamente na primeira vez que o container `playlist_db` sobe. O arquivo `database/schema.sql` contém:
- Criação das tabelas (`users`, `playlists`, `tracks`, `playlist_tracks`)
- 1 usuário de teste
- 20 tracks no catálogo
- 20 playlists com tracks associadas

Se precisar reimportar manualmente:
```bash
docker exec -i playlist_db mysql -u playlist -pplaylist123 playlist_db < database/schema.sql
```

### 5. Acessar a aplicação

Abra o browser e acesse:

| O quê | URL |
|-------|-----|
| **Aplicação (frontend)** | http://localhost:3001 |
| **API direta** | http://localhost:8080 |

### 6. Fazer login

| Campo | Valor |
|-------|-------|
| E-mail | `admin@teste.com` |
| Senha | `password` |

---

## Gerenciar os containers

```bash
# Parar (mantém os dados)
docker compose stop

# Reiniciar após parar
docker compose start

# Ver logs em tempo real
docker compose logs -f

# Ver log de um serviço específico
docker compose logs api
docker compose logs db
docker compose logs frontend

# Parar e remover containers (os dados do banco ficam no volume)
docker compose down

# Reset total — apaga tudo incluindo dados do banco
docker compose down -v
```

---

## Estrutura do projeto

```
teste_lx/
├── setup.py                    # Script de setup automático
├── docker-compose.yml
├── database/
│   └── schema.sql              # DDL + seed (20 playlists, 20 tracks)
│
├── backend/                    # API REST — PHP 5.6 + Yii 1.1
│   ├── Dockerfile
│   ├── composer.json           # firebase/php-jwt ^4.0
│   ├── docker-entrypoint.sh    # Baixa Yii, instala deps, inicia Apache
│   └── protected/
│       ├── config/
│       │   ├── main.php        # urlManager, JWT secret, imports
│       │   └── db.php          # Credenciais do banco
│       ├── components/
│       │   └── ApiController.php   # Base REST: CORS, sendJson, auth
│       ├── controllers/
│       │   ├── AuthController.php      # POST /auth/login
│       │   ├── PlaylistController.php  # CRUD /playlists + tracks
│       │   ├── TrackController.php     # GET /tracks
│       │   └── UserController.php      # GET/PUT /users/me
│       ├── filters/
│       │   └── AuthFilter.php      # Valida Bearer JWT em toda rota protegida
│       └── models/
│           ├── User.php
│           ├── Playlist.php
│           ├── Track.php
│           └── PlaylistTrack.php
│
└── frontend/                   # SPA — AngularJS 1.x + Bootstrap 3
    ├── index.html              # Shell: CDNs, navbar, ng-view
    ├── css/custom.css          # Tema escuro estilo Spotify
    └── app/
        ├── app.js              # Módulo Angular + interceptor JWT + guard de rotas
        ├── routes.js           # Definição das rotas ($routeProvider)
        ├── controllers/
        │   ├── LoginCtrl.js
        │   ├── DashboardCtrl.js    # Grid de playlists + modais de criar/editar
        │   ├── PlaylistCtrl.js     # Detalhe + gerenciar tracks
        │   └── SettingsCtrl.js     # Editar perfil e senha
        ├── services/
        │   ├── AuthService.js      # login, logout, token, usuário
        │   ├── PlaylistService.js  # $http CRUD playlists
        │   ├── TrackService.js     # $http catálogo de tracks
        │   └── UserService.js      # $http perfil
        └── views/
            ├── login.html
            ├── dashboard.html
            ├── playlist.html
            ├── playlist-form.html  # Reutilizado para criar e editar
            ├── add-track.html      # Modal de busca e adição de tracks
            └── settings.html
```

---

## API Endpoints

Todas as rotas (exceto `/auth/login`) exigem header:
```
Authorization: Bearer <token>
```

| Método | Rota | Descrição |
|--------|------|-----------|
| `POST` | `/auth/login` | Login — retorna JWT |
| `GET` | `/users/me` | Perfil do usuário autenticado |
| `PUT` | `/users/me` | Atualizar nome, e-mail ou senha |
| `GET` | `/playlists` | Listar playlists do usuário |
| `POST` | `/playlists` | Criar playlist |
| `GET` | `/playlists/:id` | Detalhe da playlist |
| `PUT` | `/playlists/:id` | Editar playlist |
| `DELETE` | `/playlists/:id` | Remover playlist |
| `GET` | `/playlists/:id/tracks` | Tracks da playlist |
| `POST` | `/playlists/:id/tracks` | Adicionar track à playlist |
| `DELETE` | `/playlists/:id/tracks/:trackId` | Remover track da playlist |
| `GET` | `/tracks` | Catálogo completo (`?q=busca`) |

### Exemplo de uso com curl

```bash
# Login
curl -s -X POST http://localhost:8080/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@teste.com","password":"password"}'

# Listar playlists (substitua <TOKEN> pelo token retornado no login)
curl -s http://localhost:8080/playlists \
  -H "Authorization: Bearer <TOKEN>"
```

---

## Funcionalidades implementadas

- Login com JWT (24h de validade) e logout
- Guard de rotas no frontend (redireciona para `/login` se não autenticado)
- Dashboard com grid de playlists estilo Spotify
- Criar, editar e deletar playlists (com seleção de cor da capa)
- Visualizar tracks de uma playlist com duração
- Adicionar tracks do catálogo a uma playlist (com busca)
- Remover tracks de uma playlist
- Busca de tracks por título, artista ou álbum (`GET /tracks?q=termo`)
- Página de configurações: editar nome, e-mail e trocar senha
- Proteção IDOR no backend: usuário só acessa suas próprias playlists

---

## Troubleshooting

### "Porta já em uso"

```bash
# Windows — ver o que usa a porta
netstat -ano | findstr :8080
netstat -ano | findstr :3001

# macOS / Linux
lsof -i :8080
lsof -i :3001
```

Para trocar as portas, edite `docker-compose.yml`:
```yaml
ports:
  - "8081:80"   # troca 8080 por 8081 (porta_host:porta_container)
```

### Containers sobem mas banco está vazio

```bash
# Reimportar o schema manualmente
docker exec -i playlist_db mysql -u playlist -pplaylist123 playlist_db < database/schema.sql
```

### API retorna erro 500 sem mensagem clara

```bash
# Ver o log completo do Apache/PHP
docker compose logs api
```

### Reset completo do ambiente

```bash
docker compose down -v   # remove containers e volumes
docker compose up --build
```
