# 📚 BiblioTech

Sistema di gestione della biblioteca scolastica sviluppato come progetto scolastico.  
Permette la gestione di prestiti e restituzioni di libri, con autenticazione sicura a due fattori e recupero password.

---

## 🛠️ Tecnologie utilizzate

- **PHP 8.2** — logica di backend (procedurale)
- **MySQL 8.0** — database relazionale
- **Bootstrap 5.3** — interfaccia utente responsive
- **Mailpit** — server SMTP locale per test email
- **Docker** — containerizzazione dell'ambiente

---

## 📁 Struttura del progetto

```
bibliotech/
├── Dockerfile
├── docker-compose.yaml
├── php.ini
├── sql/
│   └── database.sql
└── src/
    ├── config_db.php
    ├── index.php
    ├── registrazione.php
    ├── conferma.php
    ├── login.php
    ├── verifica_2fa.php
    ├── reset_password.php
    ├── dashboard.php
    ├── libri.php
    ├── miei_prestiti.php
    ├── gestisci_restituzioni.php
    ├── storico_prestiti.php
    ├── logout.php
    └── css/
        └── style.css
```
Non ho considerato la documentazione nella struttura del progetto. 


---

## 🚀 Avvio del progetto

### Requisiti
- Docker Desktop installato e avviato

### Comandi

```bash
# Prima esecuzione (o dopo modifiche al database)
docker-compose down -v
docker-compose up --build -d
```

```bash
# Avvii successivi (senza modifiche al database)
docker-compose up -d
```

Aspettare circa 30 secondi per l'avvio completo del database.

---

## 🌐 Indirizzi

| Servizio | URL |
|----------|-----|
| Applicazione | http://localhost:8080 |
| Mailpit (email) | http://localhost:8025 |
| phpMyAdmin | http://localhost:8081 |

---

## 🔑 Account di test

| Username | Password | Ruolo |
|----------|----------|-------|
| admin | password | Bibliotecario |
| mario88 | password | Studente |
| luigi_v | password | Studente |

---

## ⚙️ Funzionalità

### Studente
- Registrazione con conferma account via email
- Login con autenticazione a due fattori (2FA)
- Recupero password via email
- Consultazione catalogo libri
- Prestito libri disponibili
- Visualizzazione prestiti attivi

### Bibliotecario
- Tutte le funzionalità dello studente
- Gestione restituzioni
- Visualizzazione storico completo dei prestiti

---

## 🔒 Sicurezza

- **Password** salvate in forma hash con `password_hash()` (bcrypt)
- **2FA** tramite codice OTP inviato via email ad ogni login
- **Brute force protection** — blocco account per 15 minuti dopo 5 tentativi falliti
- **Rallentamento artificiale** — `sleep(2)` ad ogni tentativo fallito
- **Sessioni** salvate nel database con IP e User-Agent
- **Controllo sessione** — ad ogni pagina viene verificato che IP e browser corrispondano al login
- **Reset password** — token univoco con scadenza 30 minuti, utilizzabile una sola volta
- **Sanificazione input** — `mysqli_real_escape_string()` su tutti gli input usati nelle query

---

## 📧 Come funziona l'email

Le email non vengono inviate realmente. Vengono intercettate da **Mailpit**, un server SMTP locale per il testing.

Per visualizzare le email:
1. Apri http://localhost:8025
2. Tutte le email inviate dall'applicazione appariranno qui

Le email vengono inviate nei seguenti casi:
- Conferma registrazione account
- Codice OTP per il login (2FA)
- Link per il reset della password

---

## 🗄️ Schema del database

| Tabella | Descrizione |
|---------|-------------|
| `utenti` | Anagrafica utenti con credenziali e stato account |
| `libri` | Catalogo libri con gestione copie |
| `prestiti` | Registro prestiti attivi e conclusi |
| `sessioni` | Sessioni attive con IP e User-Agent |
| `reset_password` | Token per il recupero password |

---

## 👨‍💻 Progetto Scolastico 2026
