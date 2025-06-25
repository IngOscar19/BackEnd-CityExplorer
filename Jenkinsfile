pipeline {
    agent any

    environment {
        COMPOSER_ALLOW_SUPERUSER = 1
    }

    stages {
        stage('Levantar contenedores') {
            steps {
                echo "🚀 Levantando Laravel, MySQL y phpMyAdmin..."
                sh 'docker-compose -f City.yml up -d'
                sh 'sleep 15' 
            }
        }

        stage('Instalar dependencias Laravel') {
            steps {
                echo "📦 Instalando dependencias PHP..."
                sh 'docker compose exec -T app composer install'
                echo "🔑 Generando clave de la app..."
                sh 'docker compose exec -T app cp .env.example .env'
                sh 'docker compose exec -T app php artisan key:generate'
            }
        }

        stage('Migraciones') {
            steps {
                echo "🧬 Ejecutando migraciones..."
                sh 'docker compose exec -T app php artisan migrate --force'
            }
        }

        stage('Ejecutar pruebas') {
            steps {
                echo "🧪 Ejecutando pruebas..."
                sh 'docker compose exec -T app php artisan test'
            }
        }

        stage('Finalizar') {
            steps {
                echo "✅ Pipeline completado correctamente"
            }
        }
    }

    post {
        always {
            echo "🧹 Limpiando contenedores..."
            sh 'docker compose down'
        }
    }
}