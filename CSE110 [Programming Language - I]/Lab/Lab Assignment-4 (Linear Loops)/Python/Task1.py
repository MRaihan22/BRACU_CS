n = int(input("Input number of terms: "))
odd = 1
sum = 0
print("The odd numbers are: ")

for i in range(0, n):
    print(odd)
    sum += odd
    odd += 2
print("The Sum of odd Natural Numbers up to", n, "terms is:" , sum)
