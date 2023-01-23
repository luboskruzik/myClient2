node {
    stage('preparation') {
        // Checkout the master branch
        git branch: 'master', url: 'https://github.com/luboskruzik/myClient2.git'
    }
    stage("composer_install") {
        sh 'composer install'
    }
    stage("npm_install") {
        sh 'npm install'
        sh 'npm run build'
    }
    stage("phpunit") {
        sh 'php bin/phpunit --log-junit reports/report.xml'
        junit 'reports/report.xml'
    }
}
