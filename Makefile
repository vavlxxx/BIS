.PHONY: help init

help:
	@echo "Available commands:"
	@echo "  make init    - Create default .env files for the database"

init:
	@powershell -NoProfile -Command "if (!(Test-Path .env.mysql)) { Set-Content -Path .env.mysql -Value 'MYSQL_DATABASE=db`nMYSQL_USER=user`nMYSQL_PASSWORD=pwd`nMYSQL_ROOT_PASSWORD=rootpwd' -Encoding UTF8; Write-Host '[OK] Created .env.mysql' } else { Write-Host '[SKIP] .env.mysql already exists' }"
	@powershell -NoProfile -Command "if (!(Test-Path .env.pma)) { Set-Content -Path .env.pma -Value 'PMA_HOST=mysql`nPMA_PORT=3306`nPMA_USER=user`nPMA_PASSWORD=rootpwd' -Encoding UTF8; Write-Host '[OK] Created .env.pma' } else { Write-Host '[SKIP] .env.pma already exists' }"
	@powershell -NoProfile -Command "if (!(Test-Path .env.wordpress)) { Set-Content -Path .env.wordpress -Value 'WORDPRESS_DB_HOST=mysql:3306`nWORDPRESS_DB_USER=user`nWORDPRESS_DB_PASSWORD=pwd`nWORDPRESS_DB_NAME=db`nBIS_DADATA_API_KEY=`nBIS_DADATA_SECRET_KEY=' -Encoding UTF8; Write-Host '[OK] Created .env.wordpress' } else { Write-Host '[SKIP] .env.wordpress already exists' }"
	@powershell -NoProfile -Command "Write-Host '=== Initialization complete! ==='"
