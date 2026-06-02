const btnNewBucket = document.getElementById('newBucket')
const btnNewCredential = document.getElementById('newCredential')

btnNewBucket.addEventListener('click', () => {
    window.location.href = '/buckets'
})

btnNewCredential.addEventListener('click', () => {
    window.location.href = '/credentials'
})

