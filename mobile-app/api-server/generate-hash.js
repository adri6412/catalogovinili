const bcrypt = require('bcrypt');

const password = 'Fd883d0ffa.';

bcrypt.hash(password, 10, (err, hash) => {
    if (err) {
        console.error('Error:', err);
        return;
    }
    console.log('Password:', password);
    console.log('Hash:', hash);
    console.log('\nSQL Query to update:');
    console.log(`UPDATE utenti SET password = '${hash}' WHERE username = 'adriano';`);
});
