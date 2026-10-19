n = int(input("The value of N: "))
sum = 0
y = 0

for i in range(1, n+1):
    sum += i
    y -= sum

print("The value of y:", y)