while True:
    num = int(input("Enter Number: "))
    if num % 2 != 0:
        break
    divCount = 0
    for i in range(1, num+1):
        if num % i == 0:
            divCount += 1
    print(num, "has", divCount, "divisors")