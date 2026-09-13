@echo off
REM Wrapper para que Cursor/VS Code formatee PHP vía Docker
cd /d "%~dp0.."
docker compose exec -T php vendor/bin/php-cs-fixer %*
