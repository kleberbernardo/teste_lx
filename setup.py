#!/usr/bin/env python3
"""
PlaylistApp - Setup automatico
Verifica pre-requisitos, sobe os containers e abre o browser.
"""

import subprocess
import sys
import time
import os
import platform
import urllib.request
import urllib.error

# Forca UTF-8 no stdout (Python 3.7+)
if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")

# ------------------------------------------------------------------ #
#  Cores no terminal                                                   #
# ------------------------------------------------------------------ #
if platform.system() == "Windows":
    os.system("color")   # habilita ANSI no Windows 10+

GREEN  = "\033[92m"
YELLOW = "\033[93m"
RED    = "\033[91m"
CYAN   = "\033[96m"
BOLD   = "\033[1m"
RESET  = "\033[0m"

def ok(msg):    print(f"  {GREEN}[OK]{RESET}  {msg}")
def info(msg):  print(f"  {CYAN}[..]{RESET}  {msg}")
def warn(msg):  print(f"  {YELLOW}[!!]{RESET}  {msg}")
def fail(msg):  print(f"  {RED}[XX]{RESET}  {msg}")
def title(msg): print(f"\n{BOLD}{CYAN}{msg}{RESET}\n")
def line():     print("  " + "-" * 54)

# ------------------------------------------------------------------ #
#  Helpers                                                             #
# ------------------------------------------------------------------ #

def run(cmd, capture=True, check=False):
    return subprocess.run(
        cmd, shell=True,
        stdout=subprocess.PIPE if capture else None,
        stderr=subprocess.PIPE if capture else None,
        text=True
    )

def check_url(url, timeout=3):
    try:
        urllib.request.urlopen(url, timeout=timeout)
        return True
    except urllib.error.HTTPError:
        # Qualquer resposta HTTP (4xx, 5xx) significa que o servidor está no ar
        return True
    except Exception:
        return False

def open_browser(url):
    system = platform.system()
    try:
        if system == "Windows":
            os.startfile(url)
        elif system == "Darwin":
            subprocess.Popen(["open", url])
        else:
            subprocess.Popen(["xdg-open", url])
    except Exception:
        pass

# ------------------------------------------------------------------ #
#  1. Docker instalado?                                                #
# ------------------------------------------------------------------ #

def check_docker_installed():
    r = run("docker --version")
    if r.returncode != 0:
        fail("Docker não encontrado.")
        print()
        print("  Instale o Docker Desktop em:")
        print(f"  {CYAN}https://www.docker.com/products/docker-desktop/{RESET}")
        print()
        if platform.system() == "Windows":
            print("  Após instalar, reinicie o computador e rode este script novamente.")
        sys.exit(1)
    version = r.stdout.strip()
    ok(f"Docker instalado  ({version})")

# ------------------------------------------------------------------ #
#  2. Docker rodando?                                                  #
# ------------------------------------------------------------------ #

def check_docker_running():
    r = run("docker info")
    if r.returncode != 0:
        fail("Docker Desktop não está rodando.")
        print()
        print("  Abra o Docker Desktop e aguarde a baleia ficar estável,")
        print("  depois rode este script novamente.")
        sys.exit(1)
    ok("Docker Desktop está rodando")

# ------------------------------------------------------------------ #
#  3. Verificar portas livres                                          #
# ------------------------------------------------------------------ #

def check_ports():
    import socket
    ports = {"3001": "Frontend", "8080": "API", "3306": "MySQL"}
    blocked = []

    for port, name in ports.items():
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        sock.settimeout(1)
        result = sock.connect_ex(("127.0.0.1", int(port)))
        sock.close()
        if result == 0:
            blocked.append((port, name))

    if blocked:
        # Verificar se já são containers nossos
        r = run("docker compose ps --format json")
        already_ours = r.returncode == 0 and "playlist" in r.stdout

        if already_ours:
            warn("Containers já estão rodando — pulando build.")
            return True   # sinaliza: pular docker compose up
        else:
            warn("Portas em uso por outro processo:")
            for port, name in blocked:
                print(f"       Porta {port} ({name}) está ocupada")
            print()
            print("  Encerre o processo que usa essas portas e tente novamente.")
            print("  Ou edite as portas no docker-compose.yml.")
            sys.exit(1)

    ok("Portas 3001, 8080 e 3306 estão livres")
    return False

# ------------------------------------------------------------------ #
#  4. Subir os containers                                              #
# ------------------------------------------------------------------ #

def start_containers():
    info("Subindo containers com Docker Compose...")
    info("(Na primeira vez leva 3–8 min para baixar imagens)")
    print()

    # Roda docker compose up em streaming para mostrar progresso
    proc = subprocess.Popen(
        "docker compose up --build -d",
        shell=True,
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
        text=True
    )

    keywords_show = ["Pulling", "Building", "Installing", "Starting", "Created", "Started", "Error", "error"]

    for line_out in proc.stdout:
        stripped = line_out.strip()
        if any(k.lower() in stripped.lower() for k in keywords_show) and stripped:
            short = stripped[:72] + ("…" if len(stripped) > 72 else "")
            print(f"    {YELLOW}{short}{RESET}")

    proc.wait()

    if proc.returncode != 0:
        fail("docker compose up falhou.")
        print()
        print("  Veja o log completo com:")
        print("    docker compose logs")
        sys.exit(1)

    ok("Containers iniciados")

# ------------------------------------------------------------------ #
#  5. Aguardar serviços ficarem prontos                                #
# ------------------------------------------------------------------ #

def wait_for_services():
    services = [
        ("http://localhost:8080/auth/login", "API (backend)"),
        ("http://localhost:3001/",           "Frontend"),
    ]

    print()
    info("Aguardando serviços ficarem prontos...")

    for url, name in services:
        sys.stdout.write(f"    Esperando {name} ")
        sys.stdout.flush()
        waited = 0
        max_wait = 90

        while waited < max_wait:
            if check_url(url):
                print(f" {GREEN}pronto{RESET} ({waited}s)")
                break
            sys.stdout.write(".")
            sys.stdout.flush()
            time.sleep(2)
            waited += 2
        else:
            print(f" {RED}timeout{RESET}")
            fail(f"{name} não respondeu em {max_wait}s.")
            print()
            print("  Verifique os logs:")
            print("    docker compose logs")
            sys.exit(1)

# ------------------------------------------------------------------ #
#  6. Checar login da API                                              #
# ------------------------------------------------------------------ #

def check_api_login():
    import json
    try:
        data = b'{"email":"admin@teste.com","password":"password"}'
        req  = urllib.request.Request(
            "http://localhost:8080/auth/login",
            data=data,
            headers={"Content-Type": "application/json"},
            method="POST"
        )
        with urllib.request.urlopen(req, timeout=5) as resp:
            body = json.loads(resp.read())
            if "token" in body:
                ok("Login de teste funcionando")
                return
    except Exception:
        pass
    warn("API respondeu mas login retornou erro — verifique o banco.")

# ------------------------------------------------------------------ #
#  Main                                                                #
# ------------------------------------------------------------------ #

def main():
    print()
    print(f"{BOLD}{CYAN}==========================================")
    print(f"     PlaylistApp  -  Setup Automatico     ")
    print(f"=========================================={RESET}")

    # --- Pré-requisitos ---
    title("1/4  Verificando pré-requisitos")
    check_docker_installed()
    check_docker_running()
    skip_build = check_ports()

    # --- Containers ---
    title("2/4  Subindo containers")
    if skip_build:
        ok("Containers já estão rodando — nenhuma ação necessária")
    else:
        start_containers()

    # --- Aguardar ---
    title("3/4  Aguardando serviços")
    wait_for_services()
    check_api_login()

    # --- Pronto ---
    title("4/4  Tudo pronto!")
    line()
    print(f"  {BOLD}Frontend:{RESET}  {CYAN}http://localhost:3001{RESET}")
    print(f"  {BOLD}API:{RESET}       {CYAN}http://localhost:8080{RESET}")
    line()
    print(f"  {BOLD}Login de teste:{RESET}")
    print(f"    E-mail: {CYAN}admin@teste.com{RESET}")
    print(f"    Senha:  {CYAN}password{RESET}")
    line()

    # Abrir browser automaticamente
    print()
    info("Abrindo o browser...")
    time.sleep(1)
    open_browser("http://localhost:3001")

    print()
    print(f"  Para parar os containers:")
    print(f"    {YELLOW}docker compose stop{RESET}")
    print()

if __name__ == "__main__":
    try:
        main()
    except KeyboardInterrupt:
        print(f"\n\n  {YELLOW}Cancelado pelo usuário.{RESET}\n")
        sys.exit(0)
