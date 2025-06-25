pipeline {
    agent any

    environment {
        COMPOSER_ALLOW_SUPERUSER = 1
        DOCKER_COMPOSE_CMD = 'docker-compose -f City.yml'
    }

    stages {
        stage('Preparar entorno') {
            steps {
                script {
                    // Verificar que Docker esté instalado y accesible
                    sh 'docker --version'
                    
                    // Limpiar contenedores previos por si existen
                    sh "${DOCKER_COMPOSE_CMD} down || true"
                }
            }
        }

        stage('Levantar contenedores') {
            steps {
                script {
                    echo "🚀 Levantando Laravel, MySQL y phpMyAdmin..."
                    sh "${DOCKER_COMPOSE_CMD} up -d"
                    
                    // Esperar con verificación en lugar de sleep fijo
                    sh '''
                        attempts=0
                        until ${DOCKER_COMPOSE_CMD} exec -T app php --version || [ $attempts -eq 10 ]; do
                            attempts=$((attempts+1))
                            sleep 5
                            echo "Esperando que el contenedor de Laravel esté listo (intento $attempts/10)..."
                        done
                        if [ $attempts -eq 10 ]; then
                            echo "❌ El contenedor no se inició correctamente"
                            exit 1
                        fi
                    '''
                }
            }
        }

        stage('Instalar dependencias') {
            steps {
                script {
                    echo "📦 Instalando dependencias PHP..."
                    sh "${DOCKER_COMPOSE_CMD} exec -T app composer install --no-interaction --prefer-dist --optimize-autoloader"
                    
                    echo "🔑 Configurando entorno..."
                    sh """
                        ${DOCKER_COMPOSE_CMD} exec -T app cp .env.example .env || true
                        ${DOCKER_COMPOSE_CMD} exec -T app php artisan key:generate
                    """
                }
            }
        }

        stage('Configurar base de datos') {
            steps {
                script {
                    echo "🛠️ Configurando base de datos..."
                    sh """
                        ${DOCKER_COMPOSE_CMD} exec -T app php artisan config:clear
                        ${DOCKER_COMPOSE_CMD} exec -T app php artisan cache:clear
                        ${DOCKER_COMPOSE_CMD} exec -T app php artisan migrate --force
                    """
                }
            }
        }

        stage('Ejecutar pruebas') {
            steps {
                script {
                    echo "🧪 Ejecutando pruebas..."
                    sh "${DOCKER_COMPOSE_CMD} exec -T app php artisan test"
                }
            }
        }
    }

    post {
        always {
            echo "🧹 Limpiando contenedores..."
            sh "${DOCKER_COMPOSE_CMD} down"
        }
        failure {
            echo "❌ Pipeline falló - Revisar logs para detalles"
            // Opcional: Notificar por email/Slack/etc
        }
    }
}