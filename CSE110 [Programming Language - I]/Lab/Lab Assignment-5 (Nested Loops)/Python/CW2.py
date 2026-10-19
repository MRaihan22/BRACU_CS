n = int(input("N = "))
num = 2
count = 0
while count < n:
    divisorCount = 0
    for i in range(1, num + 1):
        if num % i == 0:
            divisorCount += 1
    if divisorCount == 2:
        print(num)
        count += 1
    num += 1